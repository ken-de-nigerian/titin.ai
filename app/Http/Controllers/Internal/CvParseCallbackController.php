<?php

declare(strict_types=1);

namespace App\Http\Controllers\Internal;

use App\DTOs\User\CvParseCallbackData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HandleCvParseCallbackRequest;
use App\Models\UserCv;
use App\Services\User\UserCvService;
use Illuminate\Http\JsonResponse;

final class CvParseCallbackController extends Controller
{
    public function __construct(
        private readonly UserCvService $userCvService,
    ) {}

    public function __invoke(HandleCvParseCallbackRequest $request): JsonResponse
    {
        $expected = (string) config('services.livekit.internal_secret');
        $provided = (string) $request->header('X-Internal-Token', '');
        if ($expected === '' || $provided !== $expected) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        $data = CvParseCallbackData::fromValidated($request->validated());

        $userCv = UserCv::query()
            ->whereKey($data->cvId)
            ->where('user_id', $data->userId)
            ->first();

        if ($userCv === null) {
            return response()->json([
                'message' => 'CV not found.',
            ], 404);
        }

        $this->userCvService->applyParseCallback($userCv, $data);

        return response()->json([
            'message' => 'Callback accepted.',
        ]);
    }
}
