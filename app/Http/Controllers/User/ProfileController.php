<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\DTOs\User\UpdateProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\User\UserProfileService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileService $profiles,
    ) {}

    public function settings(): Response
    {
        return Inertia::render('User/Profile/Settings');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $data = UpdateProfileData::fromValidated($request->validated());
        $this->profiles->update($user, $data);

        return $this
            ->notify('success', __('Profile updated successfully.'), __('Success'))
            ->toBack();
    }
}
