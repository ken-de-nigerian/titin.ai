<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyzeSessionFeedbackRequest;
use App\Models\InterviewSession;
use App\Services\Interview\InterviewSessionService;
use App\Services\OpenAi\OpenAiSessionFeedbackService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class SessionFeedbackController extends Controller
{
    public function __construct(
        private readonly InterviewSessionService $interviewSessions,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $latest = $user === null ? null : $this->interviewSessions->latestCompletedSession($user);

        return Inertia::render('User/Interview/Feedback', [
            'feedback' => is_array($latest?->feedback_json) ? $latest->feedback_json : null,
            'sessionId' => $latest?->id,
        ]);
    }

    public function show(Request $request, InterviewSession $session): Response
    {
        $user = $request->user();
        if ($user === null || $this->interviewSessions->findOwnedSession($user, (int) $session->id) === null) {
            abort(404);
        }

        return Inertia::render('User/Interview/Feedback', [
            'feedback' => is_array($session->feedback_json) ? $session->feedback_json : null,
            'sessionId' => $session->id,
        ]);
    }

    /**
     * @throws ConnectionException
     */
    public function analyze(
        AnalyzeSessionFeedbackRequest $analyzeRequest,
        OpenAiSessionFeedbackService $feedback,
    ): RedirectResponse {
        $user = $analyzeRequest->user();
        $data = $analyzeRequest->validated();
        if ($user === null) {
            abort(404);
        }
        $session = $this->interviewSessions->findOwnedSession($user, (int) $data['interview_session_id']);
        if ($session === null) {
            abort(404);
        }

        $messages = $data['messages'] ?? [];
        $jobRole = $data['job_role'] ?? 'Interview candidate';
        $interviewType = $data['interview_type'] ?? (string) config('settings.interview.default_type', 'mixed');
        $durationSeconds = (int) ($data['duration_seconds'] ?? 0);
        $questionCount = (int) ($data['question_count'] ?? 6);

        $payload = $feedback->analyze(
            $messages,
            $jobRole,
            $interviewType,
            $durationSeconds,
            $questionCount,
        );

        $updated = $this->interviewSessions->complete(
            session: $session,
            messages: $messages,
            durationSeconds: $durationSeconds,
            feedbackPayload: $payload,
        );

        return redirect()->route('user.feedback.show', ['session' => $updated->id]);
    }
}
