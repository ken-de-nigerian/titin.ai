<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Contracts\User\ParsedCvProfileRepositoryContract;
use App\Contracts\User\UserCvRepositoryContract;
use App\DTOs\User\CvParseCallbackData;
use App\DTOs\User\StoreUserCvData;
use App\Enums\UserCvStatus;
use App\Events\UserCvStatusUpdated;
use App\Models\User;
use App\Models\UserCv;
use Illuminate\Support\Collection;

final readonly class UserCvService
{
    public function __construct(
        private UserCvRepositoryContract $userCvRepository,
        private ParsedCvProfileRepositoryContract $parsedCvProfileRepository,
    ) {}

    /** @return Collection<int, UserCv> */
    public function listForUser(User $user): Collection
    {
        return $this->userCvRepository->listForUser($user);
    }

    public function upload(User $user, StoreUserCvData $data, string $path): UserCv
    {
        $cv = $this->userCvRepository->createAsActive($user, $data->resume, $path);
        UserCvStatusUpdated::dispatch($cv);

        return $cv;
    }

    public function activate(User $user, UserCv $cv): UserCv
    {
        $updatedCv = $this->userCvRepository->activate($user, $cv);
        UserCvStatusUpdated::dispatch($updatedCv);

        return $updatedCv;
    }

    public function markStatus(UserCv $cv, UserCvStatus $status): UserCv
    {
        $updatedCv = $this->userCvRepository->markStatus($cv, $status);
        UserCvStatusUpdated::dispatch($updatedCv);

        return $updatedCv;
    }

    public function remove(UserCv $cv): void
    {
        $deletedSnapshot = $cv->replicate();
        $deletedSnapshot->setAttribute('id', $cv->id);
        $deletedSnapshot->setAttribute('status', UserCvStatus::Deleted);
        $deletedSnapshot->setAttribute('is_active', false);
        UserCvStatusUpdated::dispatch($deletedSnapshot);

        $this->userCvRepository->delete($cv);
    }

    public function applyParseCallback(UserCv $cv, CvParseCallbackData $data): UserCv
    {
        $updatedCv = $this->markStatus($cv, $data->status);

        if ($data->status === UserCvStatus::Parsed && is_array($data->profile)) {
            $this->parsedCvProfileRepository->createForCv(
                cv: $updatedCv,
                schemaVersion: $data->schemaVersion,
                profile: $data->profile,
            );
        }

        return $updatedCv;
    }
}
