<?php

declare(strict_types=1);

namespace App\Services\Interview;

use App\DTOs\Interview\IssueInterviewTokenData;
use App\Models\ParsedCvProfile;
use App\Models\User;
use App\Models\UserCv;
use Illuminate\Support\Str;

final class InterviewPromptContextService
{
    /**
     * @return array{
     *   schema_version: string,
     *   session: array{question_count: int, concise_feedback: bool, planned_duration_seconds: int},
     *   candidate: array{user_id: int, name: string, job_role: string, seniority_level: string},
     *   interview: array{mode: string, mode_label: string, type: string, type_label: string, type_context: string},
     *   cv: array{has_uploaded_cv: bool, has_parsed_profile: bool, skills: array<int, string>, summary: string},
     *   instructions: array{context_notes: string, fallback_mode: string}
     * }
     */
    public function build(User $user, ?IssueInterviewTokenData $data = null): array
    {
        $modes = (array) config('settings.interview.modes', []);
        $defaultMode = (string) config('settings.interview.default_mode', 'simulation');
        $resolvedMode = trim($data?->interviewMode ?? $defaultMode);
        if ($resolvedMode === '' || ! array_key_exists($resolvedMode, $modes)) {
            $resolvedMode = $defaultMode;
        }

        $types = (array) config('settings.interview.types', []);
        $typeContextMap = (array) config('settings.interview.type_context', []);
        $defaultType = (string) config('settings.interview.default_type', 'mixed');
        $resolvedType = trim((string) ($data?->interviewType ?? $user->interview_type ?? $defaultType));
        if ($resolvedType === '' || ! array_key_exists($resolvedType, $types)) {
            $resolvedType = $defaultType;
        }

        $minDuration = (int) config('settings.interview.min_duration_minutes', 5);
        $maxDuration = (int) config('settings.interview.max_duration_minutes', 120);
        $defaultDurationMinutes = (int) config('settings.interview.default_duration_minutes', 25);
        $defaultDurationMinutes = max($minDuration, min($maxDuration, $defaultDurationMinutes));

        $durationMinutes = $data?->durationMinutes ?? $user->interview_duration_minutes ?? $defaultDurationMinutes;
        $durationMinutes = max($minDuration, min($maxDuration, (int) $durationMinutes));
        $plannedDurationSeconds = $durationMinutes * 60;

        $defaultQuestionCount = (int) config('settings.interview.default_question_count', 6);

        $minPrimaryQuestions = max(3, min(50, (int) config('settings.interview.primary_question_count_min', 4)));
        $maxConfigured = min(50, (int) config('settings.interview.primary_question_count_max', 20));
        $maxPrimaryQuestions = max($minPrimaryQuestions, $maxConfigured);
        $minutesPerPrimaryQuestion = max(1.0, (float) config('settings.interview.minutes_per_primary_question', 2.5));
        $derivedFromDuration = (int) ceil($durationMinutes / $minutesPerPrimaryQuestion);
        $derivedFromDuration = max($minPrimaryQuestions, min($maxPrimaryQuestions, $derivedFromDuration));

        if ($data?->questionCount !== null) {
            $questionCount = max(3, min($maxPrimaryQuestions, (int) $data->questionCount));
        } else {
            $questionCount = $derivedFromDuration > 0 ? $derivedFromDuration : max(
                3,
                min(20, $defaultQuestionCount),
            );
        }

        $jobRole = trim((string) ($data?->jobRole ?? $user->job_role ?? 'Software Engineer'));
        if ($jobRole === '') {
            $jobRole = 'Software Engineer';
        }

        $hasUploadedCv = UserCv::query()
            ->where('user_id', $user->id)
            ->exists();

        $skills = [];
        $summary = '';
        $latestParsedProfile = ParsedCvProfile::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if ($latestParsedProfile !== null && is_array($latestParsedProfile->profile_json)) {
            $profile = $latestParsedProfile->profile_json;
            $profileSkills = $profile['skills'] ?? null;
            if (is_array($profileSkills)) {
                $skills = array_values(array_slice(array_map(static fn ($value): string => (string) $value, $profileSkills), 0, 8));
            }

            $profileSummary = $profile['summary'] ?? null;
            if (is_string($profileSummary) && trim($profileSummary) !== '') {
                $summary = Str::limit(trim($profileSummary), 700);
            }
        }

        $contextLines = [
            sprintf('Candidate: %s', $user->name),
            sprintf('Target role: %s', $jobRole),
            sprintf('Interview type preference: %s', $resolvedType),
        ];

        if ($hasUploadedCv) {
            $contextLines[] = 'Candidate has uploaded a resume. Prioritize role-relevant, experience-based questions.';
        } else {
            $contextLines[] = 'No parsed resume context available yet. Start broad and adapt from answers.';
        }

        if ($skills !== []) {
            $contextLines[] = sprintf('Top skills: %s', implode(', ', $skills));
        }

        if ($summary !== '') {
            $contextLines[] = 'CV summary: '.$summary;
        }

        $typeContext = trim((string) ($typeContextMap[$resolvedType] ?? ''));
        if ($typeContext === '') {
            $typeContext = 'Ask role-relevant, clear, and balanced questions with concise follow-ups.';
        }

        return [
            'schema_version' => 'prompt_context.v1',
            'session' => [
                'question_count' => $questionCount,
                'planned_duration_seconds' => $plannedDurationSeconds,
                'concise_feedback' => (bool) $user->prefers_concise_feedback,
            ],
            'candidate' => [
                'user_id' => (int) $user->id,
                'name' => (string) $user->name,
                'job_role' => $jobRole,
                'seniority_level' => (string) ($user->seniority_level ?? (string) config('settings.seniority.default_level', 'mid_level')),
            ],
            'interview' => [
                'mode' => $resolvedMode,
                'mode_label' => (string) ($modes[$resolvedMode] ?? $resolvedMode),
                'type' => $resolvedType,
                'type_label' => (string) ($types[$resolvedType] ?? $resolvedType),
                'type_context' => $typeContext,
            ],
            'cv' => [
                'has_uploaded_cv' => $hasUploadedCv,
                'has_parsed_profile' => $latestParsedProfile !== null,
                'skills' => $skills,
                'summary' => $summary,
            ],
            'instructions' => [
                'context_notes' => implode("\n", $contextLines),
                'fallback_mode' => 'generic',
            ],
        ];
    }
}
