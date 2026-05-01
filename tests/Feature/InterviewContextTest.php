<?php

use App\Models\User;
use App\Models\UserCv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

uses(RefreshDatabase::class);

it('uses user cv records to determine resume availability', function () {
    config([
        'services.livekit.internal_secret' => 'test-secret',
    ]);

    $user = User::factory()->create([
        'resume_path' => null,
    ]);

    UserCv::query()->create([
        'user_id' => $user->id,
        'path' => 'resumes/1/latest.pdf',
        'client_original_name' => 'latest.pdf',
        'original_name' => 'latest_1234abcd.pdf',
        'mime' => 'application/pdf',
        'size' => 1800,
        'status' => 'parsed',
        'is_active' => true,
    ]);

    $response = $this
        ->get("/internal/users/{$user->id}/interview-context", [
            'X-Internal-Token' => 'test-secret',
        ])
        ->assertOk();

    expect((string) $response->json('context_notes'))
        ->toContain('Candidate has uploaded a resume. Prioritize role-relevant, experience-based questions.');
    expect(Arr::get($response->json(), 'prompt_context.schema_version'))->toBe('prompt_context.v1');
});

it('does not use legacy resume path alone to determine resume availability', function () {
    config([
        'services.livekit.internal_secret' => 'test-secret',
    ]);

    $user = User::factory()->create([
        'resume_path' => 'resumes/legacy.pdf',
    ]);

    $response = $this
        ->get("/internal/users/{$user->id}/interview-context", [
            'X-Internal-Token' => 'test-secret',
        ])
        ->assertOk();

    expect((string) $response->json('context_notes'))
        ->toContain('No parsed resume context available yet. Start broad and adapt from answers.');
});
