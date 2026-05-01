<?php

declare(strict_types=1);

namespace App\DTOs\Interview;

final readonly class IssueInterviewTokenData
{
    public function __construct(
        public ?string $jobRole,
        public ?string $interviewType,
    ) {}

    /**
     * @param  array{job_role?: string|null, interview_type?: string|null}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            jobRole: isset($validated['job_role']) ? trim((string) $validated['job_role']) : null,
            interviewType: isset($validated['interview_type']) ? trim((string) $validated['interview_type']) : null,
        );
    }
}
