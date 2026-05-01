<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\InterviewSession;
use App\Services\Interview\InterviewSessionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly InterviewSessionService $interviewSessions,
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $stats = $user === null ? [
            'total_sessions' => 0,
            'average_score' => null,
            'total_duration_seconds' => 0,
            'last_score' => null,
        ] : $this->interviewSessions->stats($user);

        $recentSessions = $user === null
            ? []
            : $this->interviewSessions
                ->recentCompletedSessions($user, 5)
                ->map(fn (InterviewSession $session): array => [
                    'id' => $session->id,
                    'job_role' => $session->job_role ?? 'Interview session',
                    'interview_type' => $session->interview_type ?? 'mixed',
                    'duration_seconds' => (int) $session->duration_seconds,
                    'overall_score' => is_array($session->feedback_json) && is_numeric($session->feedback_json['overall_score'] ?? null)
                        ? round((float) $session->feedback_json['overall_score'], 1)
                        : null,
                    'ended_at' => $session->ended_at?->toIso8601String(),
                ])
                ->values()
                ->all();

        return Inertia::render('User/Dashboard', [
            'sessionStats' => $stats,
            'recentSessions' => $recentSessions,
        ]);
    }
}
