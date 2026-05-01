<?php

declare(strict_types=1);

namespace App\DTOs\User;

use App\Enums\UserCvStatus;

final readonly class CvParseCallbackData
{
    /**
     * @param  array<string, mixed>|null  $profile
     */
    public function __construct(
        public int $cvId,
        public int $userId,
        public UserCvStatus $status,
        public string $schemaVersion,
        public ?array $profile,
    ) {}

    /**
     * @param  array{cv_id:int,user_id:int,status:string,schema_version?:string|null,profile?:array<string,mixed>|null}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            cvId: (int) $validated['cv_id'],
            userId: (int) $validated['user_id'],
            status: UserCvStatus::from((string) $validated['status']),
            schemaVersion: (string) ($validated['schema_version'] ?? 'v1'),
            profile: isset($validated['profile']) && is_array($validated['profile']) ? $validated['profile'] : null,
        );
    }
}
