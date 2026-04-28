<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\RegisterData;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;

final class RegisterUserAction
{
    public function execute(RegisterData $data): User
    {
        /** @var User $user */
        $user = User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'role' => UserRole::User,
            'status' => UserStatus::Active,
        ]);

        return $user;
    }
}
