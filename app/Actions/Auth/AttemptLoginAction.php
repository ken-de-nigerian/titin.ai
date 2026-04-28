<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Auth\LoginData;
use Illuminate\Support\Facades\Auth;

final class AttemptLoginAction
{
    public function execute(LoginData $data): bool
    {
        return Auth::attempt(
            ['email' => $data->email, 'password' => $data->password],
            $data->remember,
        );
    }
}
