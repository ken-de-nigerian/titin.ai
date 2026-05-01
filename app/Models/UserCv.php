<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserCvStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HigherOrderCollectionProxy;

/**
 * @property mixed $user_id
 * @property mixed $id
 * @property mixed $original_name
 * @property mixed $client_original_name
 * @property mixed $status
 * @property mixed $is_active
 * @property mixed $size
 * @property mixed $created_at
 * @property mixed $path
 * @property mixed $mime
 * @property HigherOrderCollectionProxy|mixed $user
 */
#[Fillable([
    'user_id',
    'path',
    'client_original_name',
    'original_name',
    'mime',
    'size',
    'status',
    'is_active',
])]
final class UserCv extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'size' => 'integer',
            'status' => UserCvStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parsedProfiles(): HasMany
    {
        return $this->hasMany(ParsedCvProfile::class);
    }
}
