<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\DTOs\Interview\IssueInterviewTokenData;
use App\Http\Controllers\Controller;
use App\Http\Requests\IssueInterviewTokenRequest;
use App\Services\Interview\InterviewTokenService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use RuntimeException;

final class InterviewTokenController extends Controller
{
    public function __construct(
        private readonly InterviewTokenService $tokenService,
    ) {}

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
        ]);
    }
}
