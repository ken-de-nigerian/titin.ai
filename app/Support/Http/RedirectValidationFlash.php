<?php

declare(strict_types=1);

namespace App\Support\Http;

use App\Traits\FlashNotification;
use Illuminate\Http\RedirectResponse;

/**
 * Centralizes redirects that combine validation errors with {@see FlashNotification} session keys.
 */
final class RedirectValidationFlash
{
    /**
     * @param  array<string, mixed>|string  $errors
     * @param  array<string, mixed>|null  $inputOnly  When {@see $flashRequestInput} is true and this is null, all request input is flashed.
     */
    public static function back(
        string $message,
        array|string $errors = [],
        string $title = 'Error',
        int $duration = 5000,
        ?array $inputOnly = null,
        bool $flashRequestInput = true,
    ): RedirectResponse {
        $redirect = back();

        if ($flashRequestInput) {
            $redirect = $inputOnly === null ? $redirect->withInput() : $redirect->withInput($inputOnly);
        }

        return $redirect
            ->withErrors($errors)
            ->with([
                'error' => $message,
                'title' => $title,
                'duration' => $duration,
            ]);
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @param  array<string, mixed>|string  $errors
     * @param  array<string, mixed>|null  $inputOnly
     */
    public static function route(
        string $route,
        array $parameters,
        string $message,
        array|string $errors = [],
        string $title = 'Error',
        int $duration = 5000,
        ?array $inputOnly = null,
        bool $flashRequestInput = true,
    ): RedirectResponse {
        $redirect = redirect()->route($route, $parameters);

        if ($flashRequestInput) {
            $redirect = $inputOnly === null ? $redirect->withInput() : $redirect->withInput($inputOnly);
        }

        return $redirect
            ->withErrors($errors)
            ->with([
                'error' => $message,
                'title' => $title,
                'duration' => $duration,
            ]);
    }
}
