<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Actions\User\CompleteCandidateOnboardingAction;
use App\DTOs\User\CompleteCandidateOnboardingData;
use App\Models\User;
use Illuminate\Support\Carbon;

final readonly class CandidateOnboardingService
{
    public function __construct(
        private CompleteCandidateOnboardingAction $completeOnboarding,
    ) {}

    public function complete(User $user, CompleteCandidateOnboardingData $data, Carbon $now): User
    {
        return $this->completeOnboarding->execute(
            user: $user,
            jobRole: $data->jobRole,
            interviewType: $data->interviewType,
            seniorityLevel: $data->seniorityLevel,
            completedAt: $now,
        );
    }
}
