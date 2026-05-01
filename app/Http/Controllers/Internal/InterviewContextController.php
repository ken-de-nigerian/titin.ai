<?php

declare(strict_types=1);

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Interview\InterviewPromptContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InterviewContextController extends Controller
{
    public function __construct(
        private readonly InterviewPromptContextService $promptContextService,
    ) {}

    public function __invoke(Request $request, User $user): JsonResponse
    {
        $expected = (string) config('services.livekit.internal_secret');
        $provided = (string) $request->header('X-Internal-Token', '');

        if ($expected === '' || $provided !== $expected) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }
        $promptContext = $this->promptContextService->build($user);
        /** @var array{context_notes:string} $instructions */
        $instructions = $promptContext['instructions'];

        return response()->json([
            'user_id' => $user->id,
            'context_notes' => $instructions['context_notes'],
            'prompt_context' => $promptContext,
        ]);
    }
}
