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
        /** @var array{question_count?: int, planned_duration_seconds?: int} $session */
        $session = is_array($promptContext['session'] ?? null) ? $promptContext['session'] : [];

        $plannedSeconds = $session['planned_duration_seconds'] ?? null;
        if (! is_int($plannedSeconds) || $plannedSeconds < 60) {
            $plannedSeconds = max(300, (int) config('settings.interview.default_duration_minutes', 25) * 60);
        }

        return $this->sessions->create([
            'user_id' => $user->id,
            'room_name' => $roomName,
            'status' => 'started',
            'job_role' => $candidate['job_role'] ?? null,
            'interview_mode' => $interview['mode'] ?? null,
            'interview_type' => $interview['type'] ?? null,
            'question_count' => (int) ($session['question_count'] ?? 6),
            'planned_duration_seconds' => $plannedSeconds,
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

    /**
     * All completed sessions (oldest → newest) for a score-over-time chart.
     *
     * @return array{
     *   points: list<array{ended_at: string|null, score: float|null}>,
     *   trend: array{
     *     direction: 'up'|'down'|'flat',
     *     label: string,
     *     percent: int|null
     *   }|null
     * }
     */
    public function scoreTrajectoryForDashboard(User $user): array
    {
        $sessions = $this->sessions->allCompletedSessionsChronologicalForUser($user)->values();

        /** @var list<array{ended_at: string|null, score: float|null}> $points */
        $points = $sessions->map(function (InterviewSession $session): array {
            $score = is_array($session->feedback_json) && is_numeric($session->feedback_json['overall_score'] ?? null)
                ? round((float) $session->feedback_json['overall_score'], 1)
                : null;

            $at = $session->ended_at ?? $session->started_at ?? $session->created_at;

            return [
                'ended_at' => $at?->toIso8601String(),
                'score' => $score,
            ];
        })->all();

        return [
            'points' => $points,
            'trend' => $this->scoreTrajectoryTrend($points),
        ];
    }

    /**
     * @param  list<array{ended_at: string|null, score: float|null}>  $points
     * @return array{
     *   direction: 'up'|'down'|'flat',
     *   label: string,
     *   percent: int|null
     * }|null
     */
    private function scoreTrajectoryTrend(array $points): ?array
    {
        $first = null;
        $last = null;
        $scoredCount = 0;

        foreach ($points as $point) {
            if ($point['score'] === null) {
                continue;
            }

            $scoredCount++;

            if ($first === null) {
                $first = $point['score'];
            }

            $last = $point['score'];
        }

        if ($first === null || $last === null || $scoredCount < 2) {
            return null;
        }

        $epsilon = 0.0001;

        $direction = match (true) {
            $last > $first + $epsilon => 'up',
            $last < $first - $epsilon => 'down',
            default => 'flat',
        };

        if ($direction === 'flat') {
            return [
                'direction' => 'flat',
                'label' => '0%',
                'percent' => 0,
            ];
        }

        if ($first > $epsilon) {
            $percent = (int) round(($last - $first) / $first * 100);

            return [
                'direction' => $direction,
                'label' => sprintf('%+d%%', $percent),
                'percent' => $percent,
            ];
        }

        return [
            'direction' => $direction,
            'label' => sprintf('%+.1f pts', $last - $first),
            'percent' => null,
        ];
    }
}
