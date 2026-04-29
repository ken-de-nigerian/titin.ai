<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Contracts\User\ProfilePhotoStorageContract;
use App\Contracts\User\UserProfileRepositoryContract;
use App\DTOs\User\DeleteAccountData;
use App\DTOs\User\UpdateInterviewPreferencesData;
use App\DTOs\User\UpdatePasswordData;
use App\DTOs\User\UpdateProfileDetailsData;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

final readonly class UserProfileService
{
    public function __construct(
        private UserProfileRepositoryContract $profiles,
        private ProfilePhotoStorageContract $photoStorage,
    ) {}

    public function updateProfileDetails(User $user, UpdateProfileDetailsData $data): User
    {
        $profilePhotoPath = $user->profile_photo_path;

        if ($data->profilePhoto !== null) {
            $this->photoStorage->delete($profilePhotoPath);
            $profilePhotoPath = $this->photoStorage->store($user, $data->profilePhoto);
        }

        return $this->profiles->updateProfileDetails(
            user: $user,
            name: $data->name,
            email: $data->email,
            profilePhotoPath: $profilePhotoPath,
            resetEmailVerification: $data->email !== $user->email,
        );
    }

    public function updateInterviewPreferences(User $user, UpdateInterviewPreferencesData $data): User
    {
        return $this->profiles->updateInterviewPreferences(
            user: $user,
            jobRole: $data->jobRole,
            interviewType: $data->interviewType,
            seniorityLevel: $data->seniorityLevel,
            prefersConciseFeedback: $data->prefersConciseFeedback,
        );
    }

    public function updatePassword(User $user, UpdatePasswordData $data): User
    {
        return $this->profiles->updatePassword(
            user: $user,
            password: $data->password,
        );
    }

    public function deleteAccount(User $user, DeleteAccountData $data): void
    {
        if ($data->confirmationText !== 'DELETE') {
            return;
        }

        $this->photoStorage->delete($user->profile_photo_path);

        if (is_string($user->resume_path) && $user->resume_path !== '') {
            Storage::disk('public')->delete($user->resume_path);
        }

        $this->profiles->deleteAccount($user);
    }
}
