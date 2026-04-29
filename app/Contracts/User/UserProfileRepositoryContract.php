<?php

declare(strict_types=1);

namespace App\Contracts\User;

use App\Models\User;

interface UserProfileRepositoryContract
{
    public function updateProfile(
        User $user,
        string $name,
        string $email,
        ?string $profilePhotoPath,
        bool $resetEmailVerification,
    ): User;
}
