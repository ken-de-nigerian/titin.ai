<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Actions\Auth\SendEmailVerificationNotificationAction;
use App\DTOs\Auth\RegisterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartRegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class RegisterController extends Controller
{
    public function __construct(
        private readonly RegisterUserAction $registerUser,
        private readonly SendEmailVerificationNotificationAction $sendEmailVerificationNotification,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Auth/Register', [
            'registrationPrefill' => [
                'name' => (string) old('name', ''),
                'email' => (string) old('email', ''),
            ],
        ]);
    }

    public function register(StartRegistrationRequest $request): RedirectResponse
    {
        $data = RegisterData::fromValidated($request->validated());
        $user = $this->registerUser->execute($data);

        Auth::login($user);
        $request->session()->regenerate();

        $this->sendEmailVerificationNotification->execute($user);

        return redirect()->route('verification.notice');
    }
}
