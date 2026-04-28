<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember,
    ) {}

    /**
     * @param  array{email: string, password: string, remember?: bool}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            email: $validated['email'],
            password: $validated['password'],
            remember: (bool) ($validated['remember'] ?? false),
        );
    }
}
