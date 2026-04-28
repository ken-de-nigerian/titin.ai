<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

final readonly class RegisterData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );
    }
}
