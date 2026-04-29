<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Actions\User\CompleteCandidateOnboardingAction;
use App\Contracts\User\CandidateResumeStorageContract;
use App\DTOs\User\CompleteCandidateOnboardingData;
use App\Models\User;
use Illuminate\Support\Carbon;

final readonly class CandidateOnboardingService
{
    public function __construct(
        private CandidateResumeStorageContract $resumeStorage,
        private CompleteCandidateOnboardingAction $completeOnboarding,
    ) {}

    public function complete(User $user, CompleteCandidateOnboardingData $data, Carbon $now): User
    {
        $resumePath = $data->resume ? $this->resumeStorage->store($user, $data->resume) : $user->resume_path;

        return $this->completeOnboarding->execute(
            user: $user,
            jobRole: $data->jobRole,
            interviewType: $data->interviewType,
            seniorityLevel: $data->seniorityLevel,
            resumePath: $resumePath,
            completedAt: $now,
        );
    }
}
