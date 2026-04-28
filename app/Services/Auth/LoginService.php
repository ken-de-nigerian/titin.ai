<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Actions\Auth\AssertUserEligibleForLocalLoginAction;
use App\Actions\Auth\AttemptLoginAction;
use App\Contracts\Auth\PostLoginRedirectContract;
use App\DTOs\Auth\LoginData;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final readonly class LoginService
{
    public function __construct(
        private AttemptLoginAction $attemptLogin,
        private AssertUserEligibleForLocalLoginAction $assertUserEligibleForLocalLogin,
        private PostLoginRedirectContract $postLoginRedirect,
    ) {}

    /**
     * Attempts to authenticate a user.
     *
     * @throws ValidationException
     */
    public function authenticate(LoginData $data): LoginResult
    {
        $user = User::query()->where('email', $data->email)->first();
        if ($user !== null) {
            $this->assertUserEligibleForLocalLogin->execute($user);
        }

        if (! $this->attemptLogin->execute($data)) {
            return LoginResult::invalidCredentials();
        }

        return LoginResult::success($this->postLoginRedirect->dashboardUrl());
    }
}
