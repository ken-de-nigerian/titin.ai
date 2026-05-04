<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('issues an interview token for authenticated onboarded users', function () {
    Http::fake([
        'http://localhost:5001/internal/issue-token' => Http::response([
            'token' => 'lk_test_token',
            'room' => 'room-abc123',
        ], 200),
    ]);

    config([
        'services.livekit.token_server_url' => 'http://localhost:5001',
        'services.livekit.internal_secret' => 'test-secret',
    ]);

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
        'job_role' => 'Backend Engineer',
        'interview_type' => 'technical',
        'prefers_concise_feedback' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('user.interview.token'));

    $response->assertOk()->assertJson([
        'token' => 'lk_test_token',
        'room' => 'room-abc123',
        'question_count' => 10,
        'planned_duration_seconds' => 1500,
    ]);

    Http::assertSent(function (Request $request): bool {
        $payload = $request->data();
        $promptContext = Arr::get($payload, 'prompt_context');

        return $request->url() === 'http://localhost:5001/internal/issue-token'
            && $request->hasHeader('X-Internal-Token', 'test-secret')
            && is_array($promptContext)
            && Arr::get($payload, 'planned_duration_seconds') === 1500
            && Arr::get($promptContext, 'schema_version') === 'prompt_context.v1'
            && Arr::get($promptContext, 'interview.mode') === 'simulation'
            && Arr::get($promptContext, 'interview.type') === 'technical'
            && is_string(Arr::get($promptContext, 'interview.type_context', ''))
            && Arr::get($promptContext, 'session.question_count') === 10
            && Arr::get($promptContext, 'session.planned_duration_seconds') === 1500;
    });
});

it('requires authentication for interview token requests', function () {
    $response = $this->post(route('user.interview.token'));

    $response->assertRedirect(route('login'));
});
