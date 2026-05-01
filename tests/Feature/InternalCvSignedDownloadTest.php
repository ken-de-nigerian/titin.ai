<?php

use App\Enums\UserCvStatus;
use App\Models\User;
use App\Models\UserCv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

it('allows GET on the signed internal CV download URL generated for the Python base host', function () {
    Storage::fake('public');

    Config::set('services.livekit.python_accessible_app_url', 'https://cv-signing.test');

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $diskPath = 'resumes/'.$user->id.'/doc.pdf';
    Storage::disk('public')->put($diskPath, '%PDF-1.4 test');

    $cv = UserCv::query()->create([
        'user_id' => $user->id,
        'path' => $diskPath,
        'original_name' => 'doc.pdf',
        'mime' => 'application/pdf',
        'size' => 900,
        'status' => UserCvStatus::Parsed,
        'is_active' => true,
    ]);

    $pythonBaseUrl = 'https://cv-signing.test';
    URL::useOrigin(rtrim($pythonBaseUrl, '/'));
    URL::forceScheme('https');
    try {
        $downloadUrl = URL::temporarySignedRoute(
            'internal.cv.file',
            now()->addMinutes(5),
            ['cv' => $cv->id],
            absolute: true,
        );
    } finally {
        URL::useOrigin(null);
        URL::forceScheme(null);
    }

    expect($downloadUrl)->toStartWith('https://cv-signing.test/internal/cv/files/');

    $this->get($downloadUrl)->assertOk()->assertHeader('content-type', 'application/pdf');
});
