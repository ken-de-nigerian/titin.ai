<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Actions\Auth\InvalidateAuthenticatedSessionAction;
use App\DTOs\User\DeleteAccountData;
use App\DTOs\User\UpdateInterviewPreferencesData;
use App\DTOs\User\UpdatePasswordData;
use App\DTOs\User\UpdateProfileDetailsData;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateInterviewPreferencesRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileDetailsRequest;
use App\Services\User\UserProfileService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileService $profiles,
        private readonly InvalidateAuthenticatedSessionAction $invalidateSession,
    ) {}

    public function settings(): Response
    {
        return Inertia::render('User/Profile/Settings');
    }

    public function updateProfileDetails(UpdateProfileDetailsRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $data = UpdateProfileDetailsData::fromValidated($request->validated());
        $this->profiles->updateProfileDetails($user, $data);

        return $this
            ->notify('success', __('Profile updated successfully.'), __('Success'))
            ->toBack();
    }

    public function updateInterviewPreferences(UpdateInterviewPreferencesRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $data = UpdateInterviewPreferencesData::fromValidated($request->validated());
        $this->profiles->updateInterviewPreferences($user, $data);

        return $this
            ->notify('success', __('Interview preferences updated.'), __('Success'))
            ->toBack();
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $data = UpdatePasswordData::fromValidated($request->validated());
        $this->profiles->updatePassword($user, $data);

        return $this
            ->notify('success', __('Password updated successfully.'), __('Success'))
            ->toBack();
    }

    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $data = DeleteAccountData::fromValidated($request->validated());
        $this->profiles->deleteAccount($user, $data);
        $this->invalidateSession->execute($request);

        return redirect()
            ->route('home')
            ->with([
                'success' => __('Your account has been deleted.'),
                'title' => __('Done'),
                'duration' => 5000,
            ]);
    }
}
