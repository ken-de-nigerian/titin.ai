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
        ?string $resumePath,
        Carbon $completedAt,
    ): User {
        $user->forceFill([
            'job_role' => $jobRole,
            'interview_type' => $interviewType,
            'resume_path' => $resumePath,
            'onboarding_completed_at' => $completedAt,
        ]);

        $user->save();

        return $user;
    }
}

