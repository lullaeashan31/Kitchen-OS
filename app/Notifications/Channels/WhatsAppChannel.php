<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toWhatsapp')) {
            return $notification->toWhatsapp($notifiable);
        }
    }
}
