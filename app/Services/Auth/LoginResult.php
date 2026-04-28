<?php

declare(strict_types=1);

namespace App\Services\Auth;

final readonly class LoginResult
{
    private function __construct(
        public bool $ok,
        public ?string $redirectUrl,
        public bool $invalidCredentials,
    ) {}

    public static function success(string $redirectUrl): self
    {
        return new self(ok: true, redirectUrl: $redirectUrl, invalidCredentials: false);
    }

    public static function invalidCredentials(): self
    {
        return new self(ok: false, redirectUrl: null, invalidCredentials: true);
    }
}
