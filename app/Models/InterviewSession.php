<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $feedback_json
 * @property mixed $id
 */
#[Fillable([
    'user_id',
    'room_name',
    'status',
    'job_role',
    'interview_mode',
    'interview_type',
    'question_count',
    'duration_seconds',
    'messages_json',
    'feedback_json',
    'started_at',
    'ended_at',
])]
final class InterviewSession extends Model
{
    protected function casts(): array
    {
        return [
            'question_count' => 'integer',
            'duration_seconds' => 'integer',
            'messages_json' => 'array',
            'feedback_json' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
