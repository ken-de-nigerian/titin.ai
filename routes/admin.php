<?php

/*
|--------------------------------------------------------------------------
| User Dashboard Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\DashboardController;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified'])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
    });
