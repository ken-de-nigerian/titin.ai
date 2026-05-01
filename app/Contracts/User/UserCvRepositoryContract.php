<?php

declare(strict_types=1);

namespace App\Contracts\User;

use App\Enums\UserCvStatus;
use App\Models\User;
use App\Models\UserCv;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface UserCvRepositoryContract
{
    /** @return Collection<int, UserCv> */
    public function listForUser(User $user): Collection;

    public function createAsActive(User $user, UploadedFile $resume, string $path): UserCv;

    public function activate(User $user, UserCv $cv): UserCv;

    public function markStatus(UserCv $cv, UserCvStatus $status): UserCv;

    public function delete(UserCv $cv): void;
}
