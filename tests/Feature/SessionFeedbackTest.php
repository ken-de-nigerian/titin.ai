<?php

use App\Models\InterviewSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

it('redirects to feedback after analyzing a transcript with substantive user answers', function () {
    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'overall_score' => 8.5,
                        'breakdown' => [
                            ['label' => 'Clarity', 'value' => 9],
                            ['label' => 'Structure', 'value' => 8],
                            ['label' => 'Depth', 'value' => 8],
                            ['label' => 'Impact', 'value' => 9],
                            ['label' => 'Pacing', 'value' => 8],
                        ],
                        'strengths' => ['Concrete examples'],
                        'growth_areas' => ['Add metrics'],
                        'top_insight' => 'Lead with outcomes.',
                        'improved_answers' => [],
                        'headline_title' => 'Engineer / Behavioral',
                        'session_summary_line' => '2 min · Session feedback',
                    ]),
                ],
            ]],
        ], 200),
    ]);

    config(['services.openai.api_key' => 'sk-test-key']);

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $session = InterviewSession::query()->create([
        'user_id' => $user->id,
        'room_name' => 'room-feedback-1',
        'status' => 'started',
        'job_role' => 'Software Engineer',
        'interview_mode' => 'simulation',
        'interview_type' => 'behavioral',
        'question_count' => 6,
        'started_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('user.feedback.analyze'), [
        'interview_session_id' => $session->id,
        'messages' => [
            ['speaker' => 'agent', 'text' => 'Tell me about a project.', 'at' => now()->toISOString()],
            ['speaker' => 'user', 'text' => 'I shipped a billing migration.', 'at' => now()->toISOString()],
        ],
        'duration_seconds' => 120,
        'job_role' => 'Software Engineer',
        'interview_type' => 'behavioral',
        'question_count' => 6,
    ]);

    $response->assertRedirect(route('user.feedback.show', ['session' => $session->id]));

    Http::assertSentCount(1);

    $session->refresh();
    expect($session->feedback_json['overall_score'])->toBeNumeric();
});

it('caps improved_answers at eighteen rows while keeping valid entries', function () {
    $many = [];
    for ($i = 1; $i <= 25; $i++) {
        $many[] = [
            'question' => "Question {$i}",
            'your_answer_snippet' => "Snippet {$i}",
            'suggested_rewrite' => "Rewrite {$i}",
        ];
    }

    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'overall_score' => 7.0,
                        'breakdown' => [
                            ['label' => 'Clarity', 'value' => 7],
                            ['label' => 'Structure', 'value' => 7],
                            ['label' => 'Depth', 'value' => 7],
                            ['label' => 'Impact', 'value' => 7],
                            ['label' => 'Pacing', 'value' => 7],
                        ],
                        'strengths' => ['On time'],
                        'growth_areas' => ['More depth'],
                        'top_insight' => 'Mixed signal.',
                        'improved_answers' => $many,
                        'headline_title' => 'Engineer / Behavioral',
                        'session_summary_line' => '10 min · Session feedback',
                    ]),
                ],
            ]],
        ], 200),
    ]);

    config(['services.openai.api_key' => 'sk-test-key']);

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $session = InterviewSession::query()->create([
        'user_id' => $user->id,
        'room_name' => 'room-feedback-cap',
        'status' => 'started',
        'job_role' => 'Software Engineer',
        'interview_mode' => 'simulation',
        'interview_type' => 'behavioral',
        'question_count' => 6,
        'started_at' => now(),
    ]);

    $this->actingAs($user)->post(route('user.feedback.analyze'), [
        'interview_session_id' => $session->id,
        'messages' => [
            ['speaker' => 'agent', 'text' => 'Tell me about a project.', 'at' => now()->toISOString()],
            ['speaker' => 'user', 'text' => 'I shipped a billing migration with metrics.', 'at' => now()->toISOString()],
        ],
        'duration_seconds' => 600,
        'job_role' => 'Software Engineer',
        'interview_type' => 'behavioral',
        'question_count' => 6,
    ]);

    $session->refresh();

    /** @var array<int, array<string, string>> $rows */
    $rows = $session->feedback_json['improved_answers'];

    expect($rows)->toHaveCount(18)
        ->and($rows[0]['question'])->toBe('Question 1')
        ->and($rows[17]['question'])->toBe('Question 18');
});

it('does not call OpenAI when transcript has only interviewer prompts', function () {
    config(['services.openai.api_key' => 'sk-test-key']);
    Http::fake();

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $session = InterviewSession::query()->create([
        'user_id' => $user->id,
        'room_name' => 'room-feedback-2',
        'status' => 'started',
        'job_role' => 'Engineer',
        'interview_mode' => 'simulation',
        'interview_type' => 'behavioral',
        'question_count' => 6,
        'started_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('user.feedback.analyze'), [
        'interview_session_id' => $session->id,
        'messages' => [
            ['speaker' => 'agent', 'text' => 'Tell me about yourself.', 'at' => now()->toISOString()],
        ],
        'duration_seconds' => 60,
        'job_role' => 'Engineer',
        'interview_type' => 'behavioral',
        'question_count' => 6,
    ]);

    $response->assertRedirect(route('user.feedback.show', ['session' => $session->id]));

    Http::assertNothingSent();

    $session->refresh();

    /** @var array<string, mixed> $payload */
    $payload = $session->feedback_json;

    expect($payload['partial'])->toBeTrue();
    expect($payload['overall_score'])->toBeNull();
    expect($payload['insufficient_candidate_input'])->toBeTrue()
        ->and($payload['breakdown'])->toBe([]);
});

it('does not call OpenAI when user replies are too short to score', function () {
    config(['services.openai.api_key' => 'sk-test-key']);
    Http::fake();

    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $session = InterviewSession::query()->create([
        'user_id' => $user->id,
        'room_name' => 'room-feedback-3',
        'status' => 'started',
        'job_role' => 'Engineer',
        'interview_mode' => 'simulation',
        'interview_type' => 'behavioral',
        'question_count' => 6,
        'started_at' => now(),
    ]);

    $this->actingAs($user)->post(route('user.feedback.analyze'), [
        'interview_session_id' => $session->id,
        'messages' => [
            ['speaker' => 'agent', 'text' => 'Tell me about a challenge.', 'at' => now()->toISOString()],
            ['speaker' => 'user', 'text' => 'ok yes', 'at' => now()->toISOString()],
        ],
        'duration_seconds' => 30,
        'job_role' => 'Engineer',
        'interview_type' => 'behavioral',
        'question_count' => 6,
    ]);

    Http::assertNothingSent();

    $session->refresh();
    expect($session->feedback_json['insufficient_candidate_input'])->toBeTrue();
});

it('passes session meta to the feedback page for client-side interview type labels', function () {
    $user = User::factory()->create([
        'onboarding_completed_at' => now(),
    ]);

    $session = InterviewSession::query()->create([
        'user_id' => $user->id,
        'room_name' => 'room-feedback-resolved-type',
        'status' => 'completed',
        'job_role' => 'Software Engineer',
        'interview_mode' => 'simulation',
        'interview_type' => 'technical_interview',
        'question_count' => 6,
        'started_at' => now(),
        'ended_at' => now(),
        'duration_seconds' => 90,
        'feedback_json' => [
            'overall_score' => 7.1,
            'breakdown' => [
                ['label' => 'Clarity', 'value' => 7],
            ],
            'strengths' => [],
            'growth_areas' => [],
            'top_insight' => 'Keep going.',
            'improved_answers' => [],
            'headline_title' => 'Software Engineer / technical_interview',
            'session_summary_line' => '2 min · debrief',
        ],
    ]);

    $this->actingAs($user)
        ->get(route('user.feedback.show', ['session' => $session->id]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $assert) => $assert
            ->component('User/Interview/Feedback')
            ->has('sessionMeta')
            ->where('sessionMeta.id', $session->id)
            ->where('sessionMeta.interview_type', 'technical_interview')
            ->where('feedback.headline_title', 'Software Engineer / technical_interview')
            ->has('settings.feedback.score_tier_labels.weak')
            ->has('settings.feedback.score_tier_labels.mid')
            ->has('settings.feedback.score_tier_labels.strong'));
});
