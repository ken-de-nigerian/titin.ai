<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeSessionFeedbackRequest;
use App\Services\OpenAiSessionFeedbackService;
use Inertia\Inertia;
use Inertia\Response;

class SessionFeedbackController extends Controller
{
    public function analyze(
        AnalyzeSessionFeedbackRequest $request,
        OpenAiSessionFeedbackService $feedback,
    ): Response {
        $data = $request->validated();

        $messages = $data['messages'] ?? [];
        $jobRole = $data['job_role'] ?? 'Interview candidate';
        $interviewType = $data['interview_type'] ?? 'behavioral';
        $durationSeconds = (int) ($data['duration_seconds'] ?? 0);
        $questionCount = (int) ($data['question_count'] ?? 6);

        $payload = $feedback->analyze(
            $messages,
            $jobRole,
            $interviewType,
            $durationSeconds,
            $questionCount,
        );

        return Inertia::render('Feedback', [
            'feedback' => $payload,
        ]);
    }
}
