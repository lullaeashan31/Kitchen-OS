<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RepeatedFailedLoginAlert extends Notification
{
    use Queueable;

    public function __construct(private readonly User $targetUser) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Traverse HR: repeated failed logins on '.$this->targetUser->email)
            ->line("The account {$this->targetUser->email} has had {$this->targetUser->failed_login_count} consecutive failed login attempts.")
            ->line('The account has been temporarily locked with exponential backoff.')
            ->line('If this was not the account holder, consider forcing a password reset.');
    }
}
