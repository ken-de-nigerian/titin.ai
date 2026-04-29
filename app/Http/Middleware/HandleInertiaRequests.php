<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
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
                    'types' => config('settings.interview.types', []),
                ],
                'seniority' => [
                    'default_level' => (string) config('settings.seniority.default_level', 'mid_level'),
                    'levels' => config('settings.seniority.levels', []),
                ],
            ],
        ];
    }
}
