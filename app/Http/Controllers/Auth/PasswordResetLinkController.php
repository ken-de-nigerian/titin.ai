<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPasswordResetLinkRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

final class PasswordResetLinkController extends Controller
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService,
    ) {}

    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function store(SendPasswordResetLinkRequest $request): RedirectResponse
    {
        $status = $this->passwordResetService->sendResetLink($request->validated());

        return $status === Password::RESET_LINK_SENT
            ? $this
                ->notify('success', __($status), __('Success'))
                ->toBack()
            : $this->notifyErrorWithValidation(
                __($status),
                ['email' => __($status)],
                __('Error'),
            );
    }
}
