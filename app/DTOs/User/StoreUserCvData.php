<?php

declare(strict_types=1);

namespace App\DTOs\User;

use Illuminate\Http\UploadedFile;

final readonly class StoreUserCvData
{
    public function __construct(
        public UploadedFile $resume,
    ) {}

    /**
     * @param  array{resume: UploadedFile}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            resume: $validated['resume'],
        );
    }
}
