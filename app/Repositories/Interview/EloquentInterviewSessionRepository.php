<?php

declare(strict_types=1);

namespace App\Repositories\Interview;

use App\Contracts\Interview\InterviewSessionRepositoryContract;
use App\Models\InterviewSession;
use App\Models\User;
use Illuminate\Support\Collection;

final class EloquentInterviewSessionRepository implements InterviewSessionRepositoryContract
{
    public function create(array $attributes): InterviewSession
    {
        return InterviewSession::query()->create($attributes);
    }

    public function complete(
        InterviewSession $session,
        array $messages,
        int $durationSeconds,
        array $feedbackPayload,
    ): InterviewSession {
        $session->forceFill([
            'status' => 'completed',
            'messages_json' => $messages,
            'feedback_json' => $feedbackPayload,
            'duration_seconds' => $durationSeconds,
            'ended_at' => now(),
        ])->save();

        return $session->refresh();
    }

    public function findOwnedByUser(User $user, int $sessionId): ?InterviewSession
    {
        return InterviewSession::query()
            ->whereBelongsTo($user)
            ->whereKey($sessionId)
            ->first();
    }

    public function latestCompletedForUser(User $user): ?InterviewSession
    {
        return InterviewSession::query()
            ->whereBelongsTo($user)
            ->where('status', 'completed')
            ->latest('id')
            ->first();
    }

    public function recentCompletedForUser(User $user, int $limit = 5): Collection
    {
        return InterviewSession::query()
            ->whereBelongsTo($user)
            ->where('status', 'completed')
            ->latest('id')
            ->limit(max(1, $limit))
            ->get();
    }

    public function allCompletedSessionsChronologicalForUser(User $user): Collection
    {
        return InterviewSession::query()
            ->whereBelongsTo($user)
            ->where('status', 'completed')
            ->orderByRaw('COALESCE(ended_at, started_at, created_at) asc')
            ->orderBy('id')
            ->get(['id', 'feedback_json', 'ended_at', 'started_at', 'created_at']);
    }

    public function statsForUser(User $user): array
    {
        $sessions = InterviewSession::query()
            ->whereBelongsTo($user)
            ->where('status', 'completed')
            ->get(['duration_seconds', 'feedback_json']);

        $total = $sessions->count();
        $durationSeconds = (int) $sessions->sum('duration_seconds');
        $scores = $sessions
            ->map(fn (InterviewSession $session): ?float => is_array($session->feedback_json)
                ? (is_numeric($session->feedback_json['overall_score'] ?? null)
                    ? (float) $session->feedback_json['overall_score']
                    : null)
                : null)
            ->filter(fn (?float $score): bool => $score !== null)
            ->values();

        $average = $scores->isEmpty() ? null : round((float) $scores->avg(), 1);
        $latestCompleted = $this->latestCompletedForUser($user);
        $lastScore = null;
        if ($latestCompleted !== null && is_array($latestCompleted->feedback_json) && is_numeric($latestCompleted->feedback_json['overall_score'] ?? null)) {
            $lastScore = round((float) $latestCompleted->feedback_json['overall_score'], 1);
        }

        return [
            'total_sessions' => $total,
            'average_score' => $average,
            'total_duration_seconds' => $durationSeconds,
            'last_score' => $lastScore,
        ];
    }
}
