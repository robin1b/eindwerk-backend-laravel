<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    StripeController,
};

// Web‐middleware waarop sessions en CSRF standaard actief zijn
Route::middleware('web')->group(function () {
    // Google OAuth
    Route::get('auth/redirect/google',  [AuthController::class, 'redirectToGoogle'])
        ->name('google.redirect');
    Route::get('auth/callback/google',  [AuthController::class, 'handleGoogleCallback'])
        ->name('google.callback');

    // Stripe Connect
    Route::get('admin/connect',          [StripeController::class, 'getConnectUrl'])
        ->name('stripe.connect');
    Route::get('admin/oauth/callback',  [StripeController::class, 'handleConnectCallback'])
        ->name('stripe.callback');
});
