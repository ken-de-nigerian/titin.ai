<?php

declare(strict_types=1);

namespace App\DTOs\User;

final readonly class UpdatePasswordData
{
    public function __construct(
        public string $password,
    ) {}

    /**
     * @param  array{password: string}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            password: $validated['password'],
        );
    }
}
