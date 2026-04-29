<?php

declare(strict_types=1);

namespace App\Contracts\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;

interface ProfilePhotoStorageContract
{
    public function store(User $user, UploadedFile $photo): string;

    public function delete(?string $path): void;
}
