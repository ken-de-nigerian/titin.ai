<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

interface PostLoginRedirectContract
{
    public function intendedDashboardUrl(): ?string;

    public function dashboardUrl(): string;
}
