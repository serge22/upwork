<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TelegramAuthController extends Controller
{

    public function telegramAuth(Request $request)
    {
        // Check if the request includes Telegram data
        if ($request->has('telegramData')) {
            return $this->handleTelegramAuth($request);
        }

        return back()->withErrors([
            'telegram' => 'Telegram authentication data is required.',
        ]);
    }

    /**
     * Handle Telegram authentication
     */
    protected function handleTelegramAuth(Request $request)
    {
        $telegramData = $request->input('telegramData');

        // Validate the Telegram data (implement proper validation)
        if (!$this->validateTelegramData($telegramData)) {
            return back()->withErrors([
                'telegram' => 'Invalid Telegram authentication data.',
            ]);
        }

        $user = $this->findOrCreateTelegramUser($telegramData);

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended(route('home', absolute: false));
        }

        return back()->withErrors([
            'telegram' => 'Failed to authenticate with Telegram.',
        ]);
    }

    /**
     * Validate Telegram Web App data
     *
     * Following Telegram's Web App documentation:
     * https://core.telegram.org/bots/webapps#validating-data-received-via-the-web-app
     */
    protected function validateTelegramData($initData)
    {
        if (empty($initData)) {
            return false;
        }

        // Parse the query string
        parse_str($initData, $data);

        // Check if hash exists
        if (!isset($data['hash'])) {
            return false;
        }

        // Get the hash from the data
        $receivedHash = $data['hash'];
        unset($data['hash']);

        // Sort the data by key
        ksort($data);

        // Create the data check string
        $dataCheckString = '';
        foreach ($data as $key => $value) {
            $dataCheckString .= $key . '=' . $value . "\n";
        }
        $dataCheckString = rtrim($dataCheckString, "\n");

        // Get the bot token from config
        $botToken = config('telegram.bots.mybot.token');

        // Create the secret key using HMAC-SHA-256
        $secretKey = hash_hmac('sha256', $botToken, "WebAppData", true);

        // Calculate the hash
        $calculatedHash = bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true));

        // Compare the calculated hash with the received hash
        return hash_equals($calculatedHash, $receivedHash);
    }

    /**
     * Find or create a user based on Telegram data
     */
    protected function findOrCreateTelegramUser($telegramData)
    {
        $data = [];
        parse_str($telegramData, $data);
        $userData = json_decode($data['user'], true);

        // Check if user with this Telegram ID exists
        $user = User::where('telegram_id', $userData['id'])->first();

        // If user exists, return it
        if ($user) {
            return $user;
        }

        // Create a new user
        $user = new User();
        $user->name = $userData['first_name'] . (empty($userData['last_name']) ? '' : ' ' . $userData['last_name']);
        $user->telegram_id = $userData['id'];
        $user->email = 'telegram_' . $userData['id'] . '@example.com';
        $user->password = Hash::make(Str::random(32));

        $user->save();

        return $user;
    }
}
