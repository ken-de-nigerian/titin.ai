<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\DTOs\User\CompleteCandidateOnboardingData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteCandidateOnboardingRequest;
use App\Models\UserCv;
use App\Services\User\CandidateOnboardingService;
use App\Services\User\UserCvService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

final class OnboardingController extends Controller
{
    public function __construct(
        private readonly CandidateOnboardingService $onboarding,
    ) {}

    public function show(Request $request, UserCvService $userCvService): Response
    {
        $user = $request->user();
        $cvItems = $user === null
            ? []
            : $userCvService
                ->listLatestForUser($user)
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

        return Inertia::render('User/Onboarding', [
            'prefill' => [
                'name' => $user?->name ?? '',
                'job_role' => $user?->job_role ?? '',
                'interview_type' => $user?->interview_type ?? (string) config('settings.interview.default_type', 'mixed'),
                'seniority_level' => $user?->seniority_level ?? (string) config('settings.seniority.default_level', 'mid_level'),
            ],
            'onboardingCvItems' => $cvItems,
        ]);
    }

    public function update(CompleteCandidateOnboardingRequest $request): RedirectResponse
    {
        $user = $request->user();
        if ($user === null) {
            return redirect()->route('login');
        }

        $data = CompleteCandidateOnboardingData::fromValidated($request->validated());

        $this->onboarding->complete($user, $data, Carbon::now());

        return $this
            ->notify('success', __('You’re all set — your profile is updated.'), __('Success'))
            ->toRoute('user.dashboard');
    }
}
