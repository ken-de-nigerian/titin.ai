<?php

/*
|--------------------------------------------------------------------------
| User Dashboard Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\User\CvAndResumeController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\InterviewController;
use App\Http\Controllers\User\InterviewTokenController;
use App\Http\Controllers\User\OnboardingController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\SessionFeedbackController;
use App\Http\Controllers\User\UserCvController;
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

                Route::prefix('cv')
                    ->name('cv.')
                    ->group(function () {
                        Route::get('/items', [UserCvController::class, 'index'])->name('items.index');
                        Route::post('/items', [UserCvController::class, 'store'])->name('items.store');
                        Route::delete('/items/{cv}', [UserCvController::class, 'destroy'])->name('items.destroy');
                    });
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
                    Route::post('/settings/details', 'updateProfileDetails')->name('details.update');
                    Route::post('/settings/interview-preferences', 'updateInterviewPreferences')->name('interview-preferences.update');
                    Route::post('/settings/password', 'updatePassword')->name('password.update');
                    Route::delete('/settings', 'destroy')->name('destroy');
                });

            // Cv & Resumes
            Route::prefix('cv')
                ->name('cv.')
                ->group(function () {
                    Route::get('/', [CvAndResumeController::class, 'index'])->name('index');
                    Route::get('/items', [UserCvController::class, 'index'])->name('items.index');
                    Route::post('/items', [UserCvController::class, 'store'])->name('items.store');
                    Route::post('/items/{cv}/activate', [UserCvController::class, 'activate'])->name('items.activate');
                    Route::delete('/items/{cv}', [UserCvController::class, 'destroy'])->name('items.destroy');
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
                ->group(function () {
                    Route::get('/', [InterviewController::class, 'index'])->name('index');
                    Route::post('/token', InterviewTokenController::class)
                        ->middleware('throttle:30,1')
                        ->name('token');
                });
        });
    });
