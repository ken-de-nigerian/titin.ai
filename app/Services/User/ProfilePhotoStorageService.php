<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Contracts\User\ProfilePhotoStorageContract;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class ProfilePhotoStorageService implements ProfilePhotoStorageContract
{
    public function store(User $user, UploadedFile $photo): string
    {
        return $photo->store('profile-photos', 'public');
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
