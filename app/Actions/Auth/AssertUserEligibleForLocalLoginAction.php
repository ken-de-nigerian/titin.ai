<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final readonly class AssertUserEligibleForLocalLoginAction
{
    public function execute(User $user, string $usernameField = 'email'): void
    {
        if ($user->status === UserStatus::Suspended) {
            throw ValidationException::withMessages([
                $usernameField => [__('Your account has been suspended. Please contact support for assistance.')],
            ])->status(403);
        }
    }
}
