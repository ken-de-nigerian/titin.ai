<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\DTOs\Auth\LoginData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartLoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class LoginController extends Controller
{
    public function __construct(
        private readonly LoginService $login,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(StartLoginRequest $request): RedirectResponse
    {
        $data = LoginData::fromValidated($request->validated());

        try {
            $result = $this->login->authenticate($data);
        } catch (ValidationException $e) {
            return $this->notifyErrorWithValidation(
                __('Unable to sign in.'),
                $e->errors(),
                inputOnly: $request->only('email'),
            );
        }

        if ($result->invalidCredentials) {
            return $this->notifyErrorWithValidation(
                __('The provided credentials are incorrect.'),
                ['email' => __('The provided credentials are incorrect.')],
                inputOnly: $request->only(['email', 'remember']),
            );
        }

        $request->session()->regenerate();

        return redirect()->intended($result->redirectUrl ?? route('login'));
    }
}
