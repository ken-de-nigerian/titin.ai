<?php

declare(strict_types=1);

namespace App\DTOs\User;

use Illuminate\Http\UploadedFile;

final readonly class UpdateProfileDetailsData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?UploadedFile $profilePhoto,
    ) {}

    /**
     * @param  array{name: string, email: string, profile_photo?: UploadedFile|null}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            name: $validated['name'],
            email: $validated['email'],
            profilePhoto: $validated['profile_photo'] ?? null,
        );
    }
}
