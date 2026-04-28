<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\DTOs\User\CompleteCandidateOnboardingData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteCandidateOnboardingRequest;
use App\Services\User\CandidateOnboardingService;
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

    public function show(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('User/Onboarding', [
            'prefill' => [
                'name' => $user?->name ?? '',
                'job_role' => $user?->job_role ?? '',
                'interview_type' => $user?->interview_type ?? 'behavioral',
            ],
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
            ->notify('success', __('Profile saved.'), __('Success'))
            ->toRoute('user.dashboard');
    }
}

