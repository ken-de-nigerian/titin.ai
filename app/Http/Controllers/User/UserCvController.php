<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\DTOs\User\StoreUserCvData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserCvRequest;
use App\Jobs\ProcessUserCvJob;
use App\Models\UserCv;
use App\Services\User\UserCvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserCvController extends Controller
{
    public function __construct(
        private readonly UserCvService $userCvService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $items = $this->userCvService
            ->listForUser($user)
            ->map(fn (UserCv $cv): array => [
                'id' => $cv->id,
                'name' => $cv->original_name,
                'mime' => $cv->mime,
                'size' => $cv->size,
                'status' => is_string($cv->status) ? $cv->status : $cv->status->value,
                'is_active' => $cv->is_active,
                'created_at' => $cv->created_at?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }

    public function store(StoreUserCvRequest $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = StoreUserCvData::fromValidated($request->validated());
        $path = $data->resume->storePublicly(sprintf('resumes/%d', (int) $user->id), ['disk' => 'public']);
        $cv = $this->userCvService->upload($user, $data, $path);

        ProcessUserCvJob::dispatch((int) $cv->id)->afterCommit();

        return response()->json([
            'message' => 'CV uploaded.',
            'item' => [
                'id' => $cv->id,
                'name' => $cv->original_name,
                'mime' => $cv->mime,
                'size' => $cv->size,
                'status' => is_string($cv->status) ? $cv->status : $cv->status->value,
                'is_active' => $cv->is_active,
                'created_at' => $cv->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function activate(Request $request, UserCv $cv): JsonResponse
    {
        $user = $request->user();
        if ($user === null || $cv->user_id !== $user->id) {
            return response()->json([
                'message' => 'Not found.',
            ], 404);
        }

        $this->userCvService->activate($user, $cv);

        return response()->json([
            'message' => 'Active CV updated.',
        ]);
    }

    public function destroy(Request $request, UserCv $cv): JsonResponse
    {
        $user = $request->user();
        if ($user === null || $cv->user_id !== $user->id) {
            return response()->json([
                'message' => 'Not found.',
            ], 404);
        }

        $this->userCvService->remove($cv);

        return response()->json([
            'message' => 'CV removed.',
        ]);
    }
}
