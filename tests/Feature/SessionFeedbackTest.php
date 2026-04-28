<?php

use Illuminate\Support\Facades\Http;

it('renders feedback after analyzing a transcript', function () {
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

    $response = $this->post(route('feedback.analyze'), [
        'messages' => [
            ['speaker' => 'agent', 'text' => 'Tell me about a project.'],
            ['speaker' => 'user', 'text' => 'I shipped a billing migration.'],
        ],
        'duration_seconds' => 120,
        'job_role' => 'Software Engineer',
        'interview_type' => 'behavioral',
        'question_count' => 6,
    ]);

    $response->assertOk();
});
