<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Welcome to :app', ['app' => config('app.name')]))
            ->greeting(__('Welcome, :name!', ['name' => (string) ($notifiable->name ?? __('there'))]))
            ->line(__('Your account is ready and you can start practicing interviews right away.'))
            ->action(__('Go to dashboard'), route('user.dashboard'))
            ->line(__('Thanks for joining us!'));
    }
}
