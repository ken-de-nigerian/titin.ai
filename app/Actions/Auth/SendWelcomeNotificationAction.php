<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Notifications\WelcomeNotification;

final class SendWelcomeNotificationAction
{
    public function execute(User $user): void
    {
        $user->notify(new WelcomeNotification);
    }
}
