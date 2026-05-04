<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Contracts\User\UserProfileRepositoryContract;
use App\Models\User;

final class EloquentUserProfileRepository implements UserProfileRepositoryContract
{
    public function updateProfileDetails(
        User $user,
        string $name,
        string $email,
        ?string $profilePhotoPath,
        bool $resetEmailVerification,
    ): User {
        $attributes = [
            'name' => $name,
            'email' => $email,
            'profile_photo_path' => $profilePhotoPath,
        ];

        if ($resetEmailVerification) {
            $attributes['email_verified_at'] = null;
        }

        $user->forceFill($attributes)->save();

        return $user->refresh();
    }

    public function updateInterviewPreferences(
        User $user,
        ?string $jobRole,
        string $interviewType,
        string $seniorityLevel,
        bool $prefersConciseFeedback,
        int $interviewDurationMinutes,
    ): User {
        $user->forceFill([
            'job_role' => $jobRole,
            'interview_type' => $interviewType,
            'seniority_level' => $seniorityLevel,
            'prefers_concise_feedback' => $prefersConciseFeedback,
            'interview_duration_minutes' => $interviewDurationMinutes,
        ])->save();

        return $user->refresh();
    }

    public function updatePassword(
        User $user,
        string $password,
    ): User {
        $user->forceFill([
            'password' => $password,
        ])->save();

        return $user->refresh();
    }

    public function deleteAccount(User $user): void
    {
        $user->delete();
    }
}
