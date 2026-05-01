<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Contracts\User\UserCvRepositoryContract;
use App\Enums\UserCvStatus;
use App\Models\User;
use App\Models\UserCv;
use App\Support\UserCvStoredDisplayName;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

final class EloquentUserCvRepository implements UserCvRepositoryContract
{
    public function listForUser(User $user): Collection
    {
        return UserCv::query()
            ->whereBelongsTo($user)
            ->latest('id')
            ->get();
    }

    /**
     * @throws Throwable
     */
    public function createAsActive(User $user, UploadedFile $resume, string $path): UserCv
    {
        return DB::transaction(function () use ($user, $resume, $path): UserCv {
            UserCv::query()->whereBelongsTo($user)->where('is_active', true)->update(['is_active' => false]);

            $mime = $resume->getMimeType() ?? 'application/octet-stream';
            $clientOriginalName = $resume->getClientOriginalName();

            return UserCv::query()->create([
                'user_id' => $user->id,
                'path' => $path,
                'client_original_name' => $clientOriginalName,
                'original_name' => UserCvStoredDisplayName::uniqueFromClientName($clientOriginalName),
                'mime' => $mime,
                'size' => (int) $resume->getSize(),
                'status' => UserCvStatus::Uploaded,
                'is_active' => true,
            ]);
        });
    }

    /**
     * @throws Throwable
     */
    public function activate(User $user, UserCv $cv): UserCv
    {
        DB::transaction(function () use ($user, $cv): void {
            UserCv::query()->whereBelongsTo($user)->where('is_active', true)->update(['is_active' => false]);
            $cv->forceFill(['is_active' => true])->save();
        });

        return $cv->refresh();
    }

    public function markStatus(UserCv $cv, UserCvStatus $status): UserCv
    {
        $cv->forceFill([
            'status' => $status,
        ])->save();

        return $cv->refresh();
    }

    public function delete(UserCv $cv): void
    {
        $cv->delete();
    }
}
