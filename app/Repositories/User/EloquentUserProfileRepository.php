<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Contracts\User\UserProfileRepositoryContract;
use App\Models\User;

final class EloquentUserProfileRepository implements UserProfileRepositoryContract
{
    public function updateProfile(
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
}
