<?php

use App\Http\Controllers\Internal\CvFileDownloadController;
use App\Http\Controllers\Internal\CvParseCallbackController;
use App\Http\Controllers\Internal\InterviewContextController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Landing'))->name('home');
Route::get('/internal/users/{user}/interview-context', InterviewContextController::class);
Route::post('/internal/cv/parsed', CvParseCallbackController::class)->name('internal.cv.parsed');
Route::get('/internal/cv/files/{cv}', CvFileDownloadController::class)
    ->middleware('signed')
    ->name('internal.cv.file');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/user.php';
