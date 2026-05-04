<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
                'title' => fn () => $request->session()->get('title'),
                'duration' => fn () => $request->session()->get('duration'),
            ],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'email_verified' => (bool) $request->user()->hasVerifiedEmail(),
                    'job_role' => $request->user()->job_role,
                    'interview_type' => $request->user()->interview_type,
                    'seniority_level' => $request->user()->seniority_level,
                    'prefers_concise_feedback' => (bool) $request->user()->prefers_concise_feedback,
                    'interview_duration_minutes' => $request->user()->interview_duration_minutes,
                    'profile_photo_url' => $request->user()->profile_photo_path
                        ? Storage::disk('public')->url($request->user()->profile_photo_path)
                        : null,
                    'onboarding_completed' => $request->user()->onboarding_completed_at !== null,
                ] : null,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'settings' => [
                'interview' => [
                    'default_type' => (string) config('settings.interview.default_type', 'mixed'),
                    'default_mode' => (string) config('settings.interview.default_mode', 'simulation'),
                    'default_question_count' => (int) config('settings.interview.default_question_count', 6),
                    'minutes_per_primary_question' => (float) config('settings.interview.minutes_per_primary_question', 2.5),
                    'primary_question_count_min' => (int) config('settings.interview.primary_question_count_min', 4),
                    'primary_question_count_max' => (int) config('settings.interview.primary_question_count_max', 20),
                    'default_duration_minutes' => (int) config('settings.interview.default_duration_minutes', 25),
                    'min_duration_minutes' => (int) config('settings.interview.min_duration_minutes', 5),
                    'max_duration_minutes' => (int) config('settings.interview.max_duration_minutes', 120),
                    'duration_presets' => array_values(array_map('intval', (array) config('settings.interview.duration_presets', []))),
                    'types' => config('settings.interview.types', []),
                    'modes' => config('settings.interview.modes', []),
                ],
                'seniority' => [
                    'default_level' => (string) config('settings.seniority.default_level', 'mid_level'),
                    'levels' => config('settings.seniority.levels', []),
                ],
                'feedback' => [
                    'score_tier_weak_below' => (float) config('settings.feedback.score_tier_weak_below', 5),
                    'score_tier_strong_from' => (float) config('settings.feedback.score_tier_strong_from', 7),
                    'score_tier_labels' => [
                        'weak' => (string) config('settings.feedback.score_tier_labels.weak', 'Needs focus'),
                        'mid' => (string) config('settings.feedback.score_tier_labels.mid', 'Mixed signal'),
                        'strong' => (string) config('settings.feedback.score_tier_labels.strong', 'Strong signal'),
                    ],
                ],
            ],
        ];
    }
}
