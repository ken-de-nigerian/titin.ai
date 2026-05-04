<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\DTOs\Interview\IssueInterviewTokenData;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueInterviewTokenRequest;
use App\Services\Interview\InterviewSessionService;
use App\Services\Interview\InterviewTokenService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use RuntimeException;

final class InterviewTokenController extends Controller
{
    public function __construct(
        private readonly InterviewTokenService $tokenService,
        private readonly InterviewSessionService $sessionService,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function __invoke(IssueInterviewTokenRequest $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        try {
            $data = IssueInterviewTokenData::fromValidated($request->validated());
            $result = $this->tokenService->issueForUser($user, $data);
            $session = $this->sessionService->start(
                user: $user,
                roomName: (string) $result['room'],
                promptContext: (array) ($result['prompt_context'] ?? []),
            );
        } catch (ConnectionException $exception) {
            return response()->json([
                'message' => 'Unable to reach the interview token service right now:'.$exception->getMessage(),
            ], 503);
        } catch (RequestException $exception) {
            return response()->json([
                'message' => 'Unable to issue interview token right now: '.$exception->getMessage(),
            ], 503);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'token' => $result['token'],
            'room' => $result['room'],
            'interview_session_id' => $session->id,
            'question_count' => $session->question_count,
            'planned_duration_seconds' => (int) (
                $session->planned_duration_seconds
                ?? max(300, (int) config('settings.interview.default_duration_minutes', 25) * 60)
            ),
        ]);
    }
}
