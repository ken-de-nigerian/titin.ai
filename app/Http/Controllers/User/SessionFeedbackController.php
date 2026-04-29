<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyzeSessionFeedbackRequest;
use App\Services\OpenAi\OpenAiSessionFeedbackService;
use Illuminate\Http\Client\ConnectionException;
use Inertia\Inertia;
use Inertia\Response;

final class SessionFeedbackController extends Controller
{
    public function index()
    {
        return Inertia::render('User/Feedback', [
            'feedback' => null,
        ]);
    }

    /**
     * @throws ConnectionException
     */
    public function analyze(
        AnalyzeSessionFeedbackRequest $request,
        OpenAiSessionFeedbackService $feedback,
    ): Response {
        $data = $request->validated();

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

        return Inertia::render('User/Feedback', [
            'feedback' => $payload,
        ]);
    }
}
