<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\InvalidateAuthenticatedSessionAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

final class SessionController extends Controller
{
    public function __construct(
        private readonly InvalidateAuthenticatedSessionAction $invalidateSession,
    ) {}

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->invalidateSession->execute($request);

        if ($user !== null) {
            try {
                activity('user_activity')
                    ->causedBy($user)
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => (string) $request->userAgent(),
                    ])
                    ->log('user.logout');
            } catch (Throwable) {
                // Never block logout on activity log failures.
            }
        }

        return redirect()->route('login');
    }
}
