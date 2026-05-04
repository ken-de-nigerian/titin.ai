<?php

declare(strict_types=1);

namespace App\Contracts\User;

use App\Models\User;

interface UserProfileRepositoryContract
{
    public function updateProfileDetails(
        User $user,
        string $name,
        string $email,
        ?string $profilePhotoPath,
        bool $resetEmailVerification,
    ): User;

    public function updateInterviewPreferences(
        User $user,
        ?string $jobRole,
        string $interviewType,
        string $seniorityLevel,
        bool $prefersConciseFeedback,
        int $interviewDurationMinutes,
    ): User;

    public function updatePassword(
        User $user,
        string $password,
    ): User;

    public function deleteAccount(User $user): void;
}
