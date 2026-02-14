<?php

namespace App\Notifications;

use App\Models\SopItemCompletion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SopItemRejected extends Notification
{
    use Queueable;

    protected $completion;

    public function __construct(SopItemCompletion $completion)
    {
        $this->completion = $completion;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'SOP Item Rejected',
            'message' => "An item in your checklist '{$this->completion->run->checklist->name}' was rejected: {$this->completion->rejection_reason}",
            'run_id' => $this->completion->run_id,
            'checklist_id' => $this->completion->run->checklist_id,
            'type' => 'sop_rejected',
        ];
    }
}
