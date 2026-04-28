<?php

declare(strict_types=1);

namespace App\DTOs\User;

use Illuminate\Http\UploadedFile;

final readonly class CompleteCandidateOnboardingData
{
    public function __construct(
        public string $jobRole,
        public string $interviewType,
        public ?UploadedFile $resume,
    ) {}

    /**
     * @param  array{job_role: string, interview_type: string, resume?: UploadedFile|null}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            jobRole: $validated['job_role'],
            interviewType: $validated['interview_type'],
            resume: $validated['resume'] ?? null,
        );
    }
}

