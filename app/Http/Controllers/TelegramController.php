<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramAuthService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TelegramController extends Controller
{
    public function __construct(
        protected TelegramAuthService $telegramAuthService
    ) {}

    public function handle(Request $request) 
    {
        try {
            $data = $this->telegramAuthService->authenticate($request->all());
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('login')->withErrors(['telegram' => 'Failed to authenticate with Telegram.']);
        }


        $user = User::where('telegram_id', $data['id'])->first();
        if ( ! $user)
        {
            $email = sprintf('tg_%d@telegram.local', $data['id']);

            $user = User::create([
                'telegram_id' => $data['id'],
                'name' => trim($request->get('first_name') . ' ' . $request->get('last_name')),
                'email' => sprintf('tg_%d@telegram.local', $data['id']),
                'password' => Hash::make(Str::random(16)),
            ]);
        }

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
