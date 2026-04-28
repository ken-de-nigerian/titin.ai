<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCandidateOnboarded
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        if ($request->routeIs('user.onboarding.*')) {
            return $next($request);
        }

        if ($user->onboarding_completed_at === null) {
            return redirect()->route('user.onboarding.show');
        }

        return $next($request);
    }
}

