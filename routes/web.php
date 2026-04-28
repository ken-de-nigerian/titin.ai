<?php

use App\Http\Controllers\SessionFeedbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Landing'))->name('home');
Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
Route::get('/interview', fn () => Inertia::render('Interview'))->name('interview');
Route::get('/feedback', fn () => Inertia::render('Feedback', [
    'feedback' => null,
]))->name('feedback');

Route::post('/feedback/analyze', [SessionFeedbackController::class, 'analyze'])
    ->middleware('throttle:30,1')
    ->name('feedback.analyze');

// Auth routes (static forms for now)
Route::get('/login', fn () => Inertia::render('Login'))->name('login');
Route::get('/register', fn () => Inertia::render('Register'))->name('register');
Route::get('/forgot-password', fn () => Inertia::render('ForgotPassword'))->name('password.request');
Route::get('/reset-password/{token}', fn (string $token, Request $request) => Inertia::render('ResetPassword', [
    'token' => $token,
    'email' => $request->query('email', ''),
]))->name('password.reset');

// Proxy to Python token server
Route::get('/api/getToken', function (Request $request) {
    $tokenServerUrl = env('VITE_TOKEN_SERVER_URL', 'http://localhost:5001');
    $name = $request->query('name', 'guest');

    try {
        $query = array_filter([
            'name' => $name,
            'job_role' => $request->query('job_role'),
            'interview_type' => $request->query('interview_type'),
        ], fn (?string $v): bool => $v !== null && $v !== '');

        $response = Http::timeout(10)->get("{$tokenServerUrl}/getToken", $query);

        return response($response->body(), $response->status())
            ->header('Content-Type', 'text/plain');
    } catch (Exception $e) {
        return response('Token server unavailable: '.$e->getMessage(), 503)
            ->header('Content-Type', 'text/plain');
    }
});
