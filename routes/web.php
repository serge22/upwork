<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Services\UpworkProvider;
use App\Services\UpworkService;
use App\Http\Controllers\FeedController;
use Telegram\Bot\Laravel\Facades\Telegram;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Feed Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('feeds', FeedController::class)->name('index', 'home');
});

Route::get('/oauth/upwork', function () {
    $provider = new UpworkProvider();

    // Redirect the user to the authorization URL
    $authorizationUrl = $provider->getAuthorizationUrl();
    session(['oauth2state' => $provider->getState()]);

    return redirect($authorizationUrl);
});

Route::post(config('telegram.bots.mybot.token') . '/webhook', function () {
    $update = Telegram::commandsHandler(true);

    return 'ok';
});

Route::get('/oauth/upwork/callback', function (UpworkService $upwork) {
    // Check state parameter
    $state = session('oauth2state');
    if (empty($state) || request('state') !== $state) {
        request()->session()->forget('oauth2state');
        abort(403, 'Invalid state');
    }

    try {
        $token = $upwork->getAccessToken(request('code'));

        return response()->json(['access_token' => $token->getToken()]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
