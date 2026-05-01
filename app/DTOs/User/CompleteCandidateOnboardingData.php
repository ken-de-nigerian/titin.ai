<?php

declare(strict_types=1);

namespace App\DTOs\User;

final readonly class CompleteCandidateOnboardingData
{
    public function __construct(
        public string $jobRole,
        public string $interviewType,
        public string $seniorityLevel,
    ) {}

    /**
     * @param  array{job_role: string, interview_type: string, seniority_level: string}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            jobRole: $validated['job_role'],
            interviewType: $validated['interview_type'],
            seniorityLevel: $validated['seniority_level'],
        );
    }
}
