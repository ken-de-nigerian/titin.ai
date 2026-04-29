<?php

/*
|--------------------------------------------------------------------------
| User Dashboard Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\InterviewController;
use App\Http\Controllers\User\OnboardingController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\SessionFeedbackController;
use App\Http\Middleware\EnsureCandidateOnboarded;

Route::prefix('user')
    ->name('user.')
    ->middleware(['auth', 'verified'])
    ->group(function () {

        // Candidate onboarding
        Route::controller(OnboardingController::class)
            ->prefix('onboarding')
            ->name('onboarding.')
            ->group(function () {
                Route::get('/', 'show')->name('show');
                Route::post('/', 'update')->name('update');
            });

        // Dashboard
        Route::middleware([EnsureCandidateOnboarded::class])->group(function () {
            Route::get('/dashboard', DashboardController::class)->name('dashboard');

            // Profile & Settings
            Route::prefix('profile')
                ->name('profile.')
                ->controller(ProfileController::class)
                ->group(function () {
                    Route::get('/settings', 'settings')->name('settings');
                    Route::post('/settings', 'update')->name('update');
                });

            // Session Feedback
            Route::prefix('feedback')
                ->name('feedback.')
                ->controller(SessionFeedbackController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/analyze', 'analyze')
                        ->middleware('throttle:30,1')
                        ->name('analyze');
                });

            // Interview Room
            Route::prefix('interview')
                ->name('interview.')
                ->controller(InterviewController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                });
        });
    });
