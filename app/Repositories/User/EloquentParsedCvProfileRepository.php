<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Contracts\User\ParsedCvProfileRepositoryContract;
use App\Models\ParsedCvProfile;
use App\Models\UserCv;

final class EloquentParsedCvProfileRepository implements ParsedCvProfileRepositoryContract
{
    public function createForCv(UserCv $cv, string $schemaVersion, array $profile): ParsedCvProfile
    {
        return ParsedCvProfile::query()->create([
            'user_cv_id' => $cv->id,
            'user_id' => $cv->user_id,
            'schema_version' => $schemaVersion,
            'profile_json' => $profile,
        ]);
    }
}
