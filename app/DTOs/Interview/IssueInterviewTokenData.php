<?php

declare(strict_types=1);

namespace App\DTOs\Interview;

final readonly class IssueInterviewTokenData
{
    public function __construct(
        public ?string $jobRole,
        public ?string $interviewType,
        public ?int $questionCount,
        public ?string $interviewMode,
    ) {}

    /**
     * @param  array{job_role?: string|null, interview_type?: string|null, question_count?: int|null, interview_mode?: string|null}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            jobRole: isset($validated['job_role']) ? trim((string) $validated['job_role']) : null,
            interviewType: isset($validated['interview_type']) ? trim((string) $validated['interview_type']) : null,
            questionCount: isset($validated['question_count']) ? (int) $validated['question_count'] : null,
            interviewMode: isset($validated['interview_mode']) ? trim((string) $validated['interview_mode']) : null,
        );
    }
}
