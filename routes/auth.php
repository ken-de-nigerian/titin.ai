<?php

use App\Contracts\Auth\PostLoginRedirectContract;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'login')->name('login.store');
    });

    // Registration Routes
    Route::controller(RegisterController::class)->group(function () {
        Route::get('/register', 'index')->name('register');
        Route::post('/register', 'register')->name('register.store');
    });
});

/*
|--------------------------------------------------------------------------
| Session & Logout Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->controller(SessionController::class)
    ->group(function () {
        // Standard user logout
        Route::post('/logout', 'destroy')->name('logout');
    });

/*
|--------------------------------------------------------------------------
| Email Verification Routes (Laravel default)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', fn () => Inertia::render('Auth/VerifyEmail'))
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->intended(app(PostLoginRedirectContract::class)->dashboardUrl());
    })
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()?->sendEmailVerificationNotification();

        return back()->with([
            'success' => __('Verification link sent.'),
            'title' => __('Success'),
            'duration' => 5000,
        ]);
    })
        ->middleware('throttle:6,1')
        ->name('verification.send');
});
