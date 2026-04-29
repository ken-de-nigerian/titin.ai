<?php

declare(strict_types=1);

namespace App\DTOs\User;

final readonly class UpdateInterviewPreferencesData
{
    public function __construct(
        public ?string $jobRole,
        public string $interviewType,
        public string $seniorityLevel,
        public bool $prefersConciseFeedback,
    ) {}

    /**
     * @param  array{job_role?: string|null, interview_type: string, seniority_level: string, prefers_concise_feedback: bool}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            jobRole: $validated['job_role'] ?? null,
            interviewType: $validated['interview_type'],
            seniorityLevel: $validated['seniority_level'],
            prefersConciseFeedback: (bool) $validated['prefers_concise_feedback'],
        );
    }
}
