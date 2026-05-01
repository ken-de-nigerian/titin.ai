<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCv;
use App\Services\User\UserCvService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CvAndResumeController extends Controller
{
    public function index(Request $request, UserCvService $userCvService): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $cvItems = $userCvService
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
            ->values()
            ->all();

        return Inertia::render('User/CvAndResume/Index', [
            'cvItems' => $cvItems,
        ]);
    }
}
