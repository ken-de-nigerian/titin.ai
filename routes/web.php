<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Landing'))->name('home');

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

        $response = Http::timeout(10)->get("$tokenServerUrl/getToken", $query);

        return response($response->body(), $response->status())
            ->header('Content-Type', 'text/plain');
    } catch (Exception $e) {
        return response('Token server unavailable: '.$e->getMessage(), 503)
            ->header('Content-Type', 'text/plain');
    }
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/user.php';
