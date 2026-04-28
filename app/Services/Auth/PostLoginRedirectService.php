<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Contracts\Auth\PostLoginRedirectContract;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

final readonly class PostLoginRedirectService implements PostLoginRedirectContract
{
    public function intendedDashboardUrl(): ?string
    {
        if (! Auth::check()) {
            return null;
        }

        if (Gate::allows('access-admin-dashboard') && Route::has('admin.dashboard')) {
            return route('admin.dashboard');
        }

        $user = Auth::user();
        if ($user !== null && $user->role === UserRole::User && $user->onboarding_completed_at === null && Route::has('user.onboarding.show')) {
            return route('user.onboarding.show');
        }

        if (Route::has('user.dashboard')) {
            return route('user.dashboard');
        }

        return null;
    }

    public function dashboardUrl(): string
    {
        if (! Auth::check()) {
            return route('login');
        }

        $url = $this->intendedDashboardUrl();
        if ($url !== null) {
            return $url;
        }

        Auth::logout();

        return route('login');
    }
}
