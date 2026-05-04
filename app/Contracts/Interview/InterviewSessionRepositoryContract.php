<?php

declare(strict_types=1);

namespace App\Contracts\Interview;

use App\Models\InterviewSession;
use App\Models\User;
use Illuminate\Support\Collection;

interface InterviewSessionRepositoryContract
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): InterviewSession;

    /**
     * @param  array<int, array{speaker: string, text: string, at?: string|null}>  $messages
     * @param  array<string, mixed>  $feedbackPayload
     */
    public function complete(
        InterviewSession $session,
        array $messages,
        int $durationSeconds,
        array $feedbackPayload,
    ): InterviewSession;

    public function findOwnedByUser(User $user, int $sessionId): ?InterviewSession;

    public function latestCompletedForUser(User $user): ?InterviewSession;

    /** @return Collection<int, InterviewSession> */
    public function recentCompletedForUser(User $user, int $limit = 5): Collection;

    /**
     * All completed sessions for the user, oldest → newest by session timeline.
     *
     * @return Collection<int, InterviewSession>
     */
    public function allCompletedSessionsChronologicalForUser(User $user): Collection;

    /**
     * @return array{
     *   total_sessions: int,
     *   average_score: float|null,
     *   total_duration_seconds: int,
     *   last_score: float|null
     * }
     */
    public function statsForUser(User $user): array;
}
