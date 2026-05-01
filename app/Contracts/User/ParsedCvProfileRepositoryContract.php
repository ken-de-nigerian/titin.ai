<?php

declare(strict_types=1);

namespace App\Contracts\User;

use App\Models\ParsedCvProfile;
use App\Models\UserCv;

interface ParsedCvProfileRepositoryContract
{
    /**
     * @param  array<string, mixed>  $profile
     */
    public function createForCv(UserCv $cv, string $schemaVersion, array $profile): ParsedCvProfile;
}
