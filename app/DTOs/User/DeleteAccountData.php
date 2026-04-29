<?php

declare(strict_types=1);

namespace App\DTOs\User;

final readonly class DeleteAccountData
{
    public function __construct(
        public string $currentPassword,
        public string $confirmationText,
    ) {}

    /**
     * @param  array{current_password: string, confirmation_text: string}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            currentPassword: $validated['current_password'],
            confirmationText: $validated['confirmation_text'],
        );
    }
}
