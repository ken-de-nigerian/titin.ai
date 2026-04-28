<?php

declare(strict_types=1);

namespace App\Traits;

use App\Support\Http\RedirectValidationFlash;
use Illuminate\Http\RedirectResponse;

trait FlashNotification
{
    protected array $flashData = [];

    public function notify(string $type, ?string $message = null, ?string $title = null, int $duration = 5000): static
    {
        $this->flashData = [
            $type => $message,
            'title' => $title ?? ucfirst($type),
            'duration' => $duration,
        ];

        return $this;
    }

    public function toRoute(string $route, array $parameters = []): RedirectResponse
    {
        return redirect()->route($route, $parameters)->with($this->flashData);
    }

    public function to(string $url): RedirectResponse
    {
        return redirect()->to($url)->with($this->flashData);
    }

    public function toIntended(string $default): RedirectResponse
    {
        return redirect()->intended($default)->with($this->flashData);
    }

    public function toBack(): RedirectResponse
    {
        return back()->with($this->flashData);
    }

    /**
     * @param  array<string, mixed>|string  $errors
     * @param  array<string, mixed>|null  $inputOnly  When {@see $flashRequestInput} is true and this is null, all request input is flashed.
     */
    public function notifyErrorWithValidation(
        string $message,
        array|string $errors = [],
        string $title = 'Error',
        int $duration = 5000,
        ?array $inputOnly = null,
        bool $flashRequestInput = true,
    ): RedirectResponse {
        return RedirectValidationFlash::back(
            $message,
            $errors,
            $title,
            $duration,
            $inputOnly,
            $flashRequestInput,
        );
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @param  array<string, mixed>|string  $errors
     * @param  array<string, mixed>|null  $inputOnly
     */
    public function notifyErrorWithValidationToRoute(
        string $route,
        array $parameters,
        string $message,
        array|string $errors = [],
        string $title = 'Error',
        int $duration = 5000,
        ?array $inputOnly = null,
        bool $flashRequestInput = true,
    ): RedirectResponse {
        return RedirectValidationFlash::route(
            $route,
            $parameters,
            $message,
            $errors,
            $title,
            $duration,
            $inputOnly,
            $flashRequestInput,
        );
    }
}
