<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $profile_json
 */
#[Fillable([
    'user_cv_id',
    'user_id',
    'schema_version',
    'profile_json',
])]
final class ParsedCvProfile extends Model
{
    protected function casts(): array
    {
        return [
            'profile_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userCv(): BelongsTo
    {
        return $this->belongsTo(UserCv::class);
    }
}
