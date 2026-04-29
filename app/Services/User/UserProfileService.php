<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Contracts\User\ProfilePhotoStorageContract;
use App\Contracts\User\UserProfileRepositoryContract;
use App\DTOs\User\UpdateProfileData;
use App\Models\User;

final readonly class UserProfileService
{
    public function __construct(
        private UserProfileRepositoryContract $profiles,
        private ProfilePhotoStorageContract $photoStorage,
    ) {}

    public function update(User $user, UpdateProfileData $data): User
    {
        $profilePhotoPath = $user->profile_photo_path;

        if ($data->profilePhoto !== null) {
            $this->photoStorage->delete($profilePhotoPath);
            $profilePhotoPath = $this->photoStorage->store($user, $data->profilePhoto);
        }

        return $this->profiles->updateProfile(
            user: $user,
            name: $data->name,
            email: $data->email,
            profilePhotoPath: $profilePhotoPath,
            resetEmailVerification: $data->email !== $user->email,
        );
    }
}
