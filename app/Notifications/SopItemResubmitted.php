<?php

namespace App\Notifications;

use App\Models\SopItemCompletion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SopItemResubmitted extends Notification
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
            'title' => 'SOP Item Resubmitted',
            'message' => "{$this->completion->user->name} has resubmitted a photo for '{$this->completion->item->name}' in '{$this->completion->run->checklist->name}'.",
            'run_id' => $this->completion->run_id,
            'type' => 'sop_resubmitted',
        ];
    }
}
