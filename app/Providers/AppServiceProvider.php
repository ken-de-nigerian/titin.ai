<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Auth\PostLoginRedirectContract;
use App\Contracts\Interview\InterviewSessionRepositoryContract;
use App\Contracts\User\ParsedCvProfileRepositoryContract;
use App\Contracts\User\ProfilePhotoStorageContract;
use App\Contracts\User\UserCvRepositoryContract;
use App\Contracts\User\UserProfileRepositoryContract;
use App\Repositories\Interview\EloquentInterviewSessionRepository;
use App\Repositories\User\EloquentParsedCvProfileRepository;
use App\Repositories\User\EloquentUserCvRepository;
use App\Repositories\User\EloquentUserProfileRepository;
use App\Services\Auth\PostLoginRedirectService;
use App\Services\User\ProfilePhotoStorageService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PostLoginRedirectContract::class, PostLoginRedirectService::class);
        $this->app->bind(ProfilePhotoStorageContract::class, ProfilePhotoStorageService::class);
        $this->app->bind(UserProfileRepositoryContract::class, EloquentUserProfileRepository::class);
        $this->app->bind(UserCvRepositoryContract::class, EloquentUserCvRepository::class);
        $this->app->bind(ParsedCvProfileRepositoryContract::class, EloquentParsedCvProfileRepository::class);
        $this->app->bind(InterviewSessionRepositoryContract::class, EloquentInterviewSessionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
