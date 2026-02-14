<?php

namespace App\Notifications;

use App\Models\SopDailyRun;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SopChecklistCompleted extends Notification
{
    use Queueable;

    protected $run;

    public function __construct(SopDailyRun $run)
    {
        $this->run = $run;
    }

    public function via($notifiable): array
    {
        return ['database']; // Defaulting to database for the "green notification" UI
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'SOP Checklist Approved',
            'message' => "The checklist '{$this->run->checklist->name}' has been completed and auto-approved by {$this->run->user->name}.",
            'run_id' => $this->run->id,
            'type' => 'sop_success',
        ];
    }
}
