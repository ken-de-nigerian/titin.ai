<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('access-admin-dashboard', function (User $user): bool {
            return $user->role === UserRole::Admin || session()->has('admin_id');
        });

        Gate::define('access-user-dashboard', function (User $user): bool {
            return $user->role === UserRole::User || session()->has('admin_id');
        });
    }
}
