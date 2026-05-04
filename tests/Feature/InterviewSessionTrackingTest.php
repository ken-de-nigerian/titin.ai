<?php

use App\Models\InterviewSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('creates an interview session when issuing a token', function () {
    config([
        'services.livekit.token_server_url' => 'http://localhost:5001',
        'services.livekit.internal_secret' => 'test-secret',
    ]);

    Http::fake([
        'http://localhost:5001/internal/issue-token' => Http::response([
            'token' => 'lk_test_token',
            'room' => 'room-abc123',
        ], 200),
    ]);

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
        'job_role' => 'Backend Engineer',
        'interview_type' => 'technical',
    ]);

    $response = $this
        ->actingAs($user)
        ->postJson(route('user.interview.token'));

    $response
        ->assertOk()
        ->assertJson([
            'token' => 'lk_test_token',
            'room' => 'room-abc123',
        ]);

    expect($response->json('interview_session_id'))->toBeInt();

    $sessionId = (int) $response->json('interview_session_id');
    $session = InterviewSession::query()->find($sessionId);

    expect($session)->not->toBeNull();
    expect($session?->user_id)->toBe($user->id);
    expect($session?->status)->toBe('started');
    expect($session?->room_name)->toBe('room-abc123');
    expect($session?->planned_duration_seconds)->toBe(1500);
    expect($session?->question_count)->toBe(10);
});

it('completes an interview session and redirects to persisted feedback page', function () {
    config([
        'services.openai.api_key' => 'test-openai-key',
        'services.openai.feedback_model' => 'gpt-4o-mini',
    ]);

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'overall_score' => 8.4,
                            'breakdown' => [
                                ['label' => 'Clarity', 'value' => 8.2],
                            ],
                            'strengths' => ['Clear communication'],
                            'growth_areas' => ['Add more metrics'],
                            'top_insight' => 'Good structure with room for stronger quantification.',
                            'improved_answers' => [],
                            'headline_title' => 'Backend Engineer / technical',
                            'session_summary_line' => '12 minutes · tailored feedback',
                        ], JSON_THROW_ON_ERROR),
                    ],
                ],
            ],
        ], 200),
    ]);

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $session = InterviewSession::query()->create([
        'user_id' => $user->id,
        'room_name' => 'room-abc123',
        'status' => 'started',
        'job_role' => 'Backend Engineer',
        'interview_mode' => 'simulation',
        'interview_type' => 'technical',
        'question_count' => 6,
        'started_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('user.feedback.analyze'), [
            'interview_session_id' => $session->id,
            'messages' => [
                ['speaker' => 'agent', 'text' => 'Tell me about a difficult incident.', 'at' => now()->toISOString()],
                ['speaker' => 'user', 'text' => 'I handled a production outage using a rollback plan.', 'at' => now()->toISOString()],
            ],
            'duration_seconds' => 720,
            'job_role' => 'Backend Engineer',
            'interview_type' => 'technical',
            'question_count' => 6,
        ]);

    $response->assertRedirect(route('user.feedback.show', ['session' => $session->id]));

    $updated = $session->refresh();
    expect($updated->status)->toBe('completed');
    expect($updated->duration_seconds)->toBe(720);
    expect($updated->messages_json)->toBeArray();
    expect($updated->feedback_json)->toBeArray();
    expect($updated->ended_at)->not->toBeNull();
});
