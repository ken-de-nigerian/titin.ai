<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Carbon;

final class CompleteCandidateOnboardingAction
{
    public function execute(
        User $user,
        string $jobRole,
        string $interviewType,
        string $seniorityLevel,
        Carbon $completedAt,
    ): User {
        $user->forceFill([
            'job_role' => $jobRole,
            'interview_type' => $interviewType,
            'seniority_level' => $seniorityLevel,
            'onboarding_completed_at' => $completedAt,
        ]);

        $user->save();

        return $user;
    }
}
