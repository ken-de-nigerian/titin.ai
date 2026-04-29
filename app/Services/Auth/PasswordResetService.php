<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class PasswordResetService
{
    /**
     * @param  array{email: string}  $credentials
     */
    public function sendResetLink(array $credentials): string
    {
        return Password::sendResetLink($credentials);
    }

    /**
     * @param  array{email: string, password: string, password_confirmation: string, token: string}  $payload
     */
    public function resetPassword(array $payload): string
    {
        return Password::reset(
            $payload,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );
    }
}
