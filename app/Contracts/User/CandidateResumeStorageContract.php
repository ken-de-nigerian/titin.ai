<?php

declare(strict_types=1);

namespace App\Contracts\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;

interface CandidateResumeStorageContract
{
    public function store(User $user, UploadedFile $resume): string;
}

