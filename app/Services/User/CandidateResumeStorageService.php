<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Contracts\User\CandidateResumeStorageContract;
use App\Models\User;
use Illuminate\Http\UploadedFile;

final class CandidateResumeStorageService implements CandidateResumeStorageContract
{
    public function store(User $user, UploadedFile $resume): string
    {
        $directory = sprintf('resumes/%d', $user->id);

        return $resume->storePublicly($directory, [
            'disk' => 'public',
        ]);
    }
}

