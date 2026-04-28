<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class InvalidateAuthenticatedSessionAction
{
    public function execute(Request $request): void
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
    }
}
