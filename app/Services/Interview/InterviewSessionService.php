<?php

declare(strict_types=1);

namespace App\Services\Interview;

use App\Contracts\Interview\InterviewSessionRepositoryContract;
use App\Models\InterviewSession;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class InterviewSessionService
{
    public function __construct(
        private InterviewSessionRepositoryContract $sessions,
    ) {}

    /**
     * @param  array<string, mixed>  $promptContext
     */
    public function start(User $user, string $roomName, array $promptContext): InterviewSession
    {
        /** @var array{job_role?: string} $candidate */
        $candidate = is_array($promptContext['candidate'] ?? null) ? $promptContext['candidate'] : [];
        /** @var array{mode?: string,type?: string} $interview */
        $interview = is_array($promptContext['interview'] ?? null) ? $promptContext['interview'] : [];
        /** @var array{question_count?: int} $session */
        $session = is_array($promptContext['session'] ?? null) ? $promptContext['session'] : [];

        return $this->sessions->create([
            'user_id' => $user->id,
            'room_name' => $roomName,
            'status' => 'started',
            'job_role' => $candidate['job_role'] ?? null,
            'interview_mode' => $interview['mode'] ?? null,
            'interview_type' => $interview['type'] ?? null,
            'question_count' => (int) ($session['question_count'] ?? 6),
            'started_at' => now(),
        ]);
    }

    /**
     * @param  array<int, array{speaker: string, text: string, at?: string|null}>  $messages
     * @param  array<string, mixed>  $feedbackPayload
     */
    public function complete(
        InterviewSession $session,
        array $messages,
        int $durationSeconds,
        array $feedbackPayload,
    ): InterviewSession {
        return $this->sessions->complete(
            session: $session,
            messages: $messages,
            durationSeconds: $durationSeconds,
            feedbackPayload: $feedbackPayload,
        );
    }

    public function findOwnedSession(User $user, int $sessionId): ?InterviewSession
    {
        return $this->sessions->findOwnedByUser($user, $sessionId);
    }

    public function latestCompletedSession(User $user): ?InterviewSession
    {
        return $this->sessions->latestCompletedForUser($user);
    }

    /** @return Collection<int, InterviewSession> */
    public function recentCompletedSessions(User $user, int $limit = 5): Collection
    {
        return $this->sessions->recentCompletedForUser($user, $limit);
    }

    /**
     * @return array{
     *   total_sessions: int,
     *   average_score: float|null,
     *   total_duration_seconds: int,
     *   last_score: float|null
     * }
     */
    public function stats(User $user): array
    {
        return $this->sessions->statsForUser($user);
    }
}
