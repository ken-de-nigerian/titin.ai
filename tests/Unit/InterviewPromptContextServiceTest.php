<?php

declare(strict_types=1);

use App\DTOs\Interview\IssueInterviewTokenData;
use App\Models\ParsedCvProfile;
use App\Models\User;
use App\Models\UserCv;
use App\Services\Interview\InterviewPromptContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds prompt context from config user preferences and parsed cv profile', function () {
    config([
        'settings.interview.default_mode' => 'simulation',
        'settings.interview.modes' => [
            'simulation' => 'Real Interview Simulation',
            'mock' => 'Practice / Coaching',
        ],
        'settings.interview.default_type' => 'mixed',
        'settings.interview.default_question_count' => 6,
        'settings.interview.types' => [
            'technical' => 'Technical Knowledge',
            'mixed' => 'Mixed / Adaptive Mode',
        ],
        'settings.interview.type_context' => [
            'technical' => 'Focus on technical depth and concrete trade-offs.',
            'mixed' => 'Blend technical and behavioral questions.',
        ],
    ]);

    $user = User::factory()->create([
        'name' => 'Jane Candidate',
        'job_role' => 'Backend Engineer',
        'interview_type' => 'technical',
        'seniority_level' => 'mid_level',
        'prefers_concise_feedback' => true,
    ]);

    $cv = UserCv::query()->create([
        'user_id' => $user->id,
        'path' => 'resumes/1/latest.pdf',
        'client_original_name' => 'latest.pdf',
        'original_name' => 'latest_1234abcd.pdf',
        'mime' => 'application/pdf',
        'size' => 1800,
        'status' => 'parsed',
        'is_active' => true,
    ]);

    ParsedCvProfile::query()->create([
        'user_cv_id' => $cv->id,
        'user_id' => $user->id,
        'schema_version' => 'v1',
        'profile_json' => [
            'skills' => ['php', 'laravel', 'redis'],
            'summary' => 'Experienced backend engineer with distributed systems focus.',
        ],
    ]);

    $data = new IssueInterviewTokenData(
        jobRole: 'Senior Backend Engineer',
        interviewType: 'technical',
        questionCount: 8,
        interviewMode: 'simulation',
    );

    $context = app(InterviewPromptContextService::class)->build($user, $data);

    expect(Arr::get($context, 'schema_version'))->toBe('prompt_context.v1')
        ->and(Arr::get($context, 'candidate.job_role'))->toBe('Senior Backend Engineer')
        ->and(Arr::get($context, 'interview.mode'))->toBe('simulation')
        ->and(Arr::get($context, 'interview.type'))->toBe('technical')
        ->and(Arr::get($context, 'interview.type_label'))->toBe('Technical Knowledge')
        ->and(Arr::get($context, 'interview.type_context'))->toBe('Focus on technical depth and concrete trade-offs.')
        ->and(Arr::get($context, 'session.question_count'))->toBe(8)
        ->and(Arr::get($context, 'session.concise_feedback'))->toBeTrue()
        ->and(Arr::get($context, 'cv.has_uploaded_cv'))->toBeTrue()
        ->and(Arr::get($context, 'cv.has_parsed_profile'))->toBeTrue()
        ->and(Arr::get($context, 'cv.skills'))->toBe(['php', 'laravel', 'redis'])
        ->and((string) Arr::get($context, 'instructions.context_notes'))->toContain('Top skills: php, laravel, redis');
});

it('falls back safely for unknown interview type and out-of-range question count', function () {
    config([
        'settings.interview.default_mode' => 'simulation',
        'settings.interview.modes' => [
            'simulation' => 'Real Interview Simulation',
            'mock' => 'Practice / Coaching',
        ],
        'settings.interview.default_type' => 'mixed',
        'settings.interview.default_question_count' => 6,
        'settings.interview.types' => [
            'technical' => 'Technical Knowledge',
            'mixed' => 'Mixed / Adaptive Mode',
        ],
        'settings.interview.type_context' => [
            'technical' => 'Focus on technical depth and concrete trade-offs.',
            'mixed' => 'Blend technical and behavioral questions.',
        ],
    ]);

    $user = User::factory()->create([
        'job_role' => null,
        'interview_type' => null,
        'prefers_concise_feedback' => false,
    ]);

    $data = new IssueInterviewTokenData(
        jobRole: null,
        interviewType: 'unknown_custom_type',
        questionCount: 99,
        interviewMode: 'unknown_mode',
    );

    $context = app(InterviewPromptContextService::class)->build($user, $data);

    expect(Arr::get($context, 'candidate.job_role'))->toBe('Software Engineer')
        ->and(Arr::get($context, 'interview.mode'))->toBe('simulation')
        ->and(Arr::get($context, 'interview.type'))->toBe('mixed')
        ->and(Arr::get($context, 'interview.type_context'))->toBe('Blend technical and behavioral questions.')
        ->and(Arr::get($context, 'session.question_count'))->toBe(20)
        ->and(Arr::get($context, 'cv.has_uploaded_cv'))->toBeFalse()
        ->and(Arr::get($context, 'cv.has_parsed_profile'))->toBeFalse();
});
