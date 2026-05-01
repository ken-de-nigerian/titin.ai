<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Notifications\QueuedResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property mixed $status
 * @property mixed $id
 * @property mixed $profile_photo_path
 * @property mixed $email
 * @property mixed $resume_path
 * @property mixed $name
 * @property mixed $prefers_concise_feedback
 */
#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'status',
    'job_role',
    'interview_type',
    'seniority_level',
    'prefers_concise_feedback',
    'resume_path',
    'profile_photo_path',
    'onboarding_completed_at',
])]
#[Hidden(['password', 'remember_token'])]
final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'prefers_concise_feedback' => 'boolean',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPasswordNotification($token));
    }

    public function cvs(): HasMany
    {
        return $this->hasMany(UserCv::class);
    }

    public function interviewSessions(): HasMany
    {
        return $this->hasMany(InterviewSession::class);
    }
}
