<?php

namespace App\Notifications;

use App\Models\SopChecklist;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SopDeadlineMissed extends Notification
{
    use Queueable;

    protected $checklist;
    protected $staffName;

    public function __construct(SopChecklist $checklist, $staffName = 'Unassigned')
    {
        $this->checklist = $checklist;
        $this->staffName = $staffName;
    }

    public function via($notifiable): array
    {
        return ['database', \App\Notifications\Channels\WhatsAppChannel::class];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'SOP Deadline Missed',
            'message' => "The checklist '{$this->checklist->name}' assigned to {$this->staffName} has missed its deadline today.",
            'checklist_id' => $this->checklist->id,
            'staff_name' => $this->staffName,
            'type' => 'sop_overdue',
        ];
    }

    public function toWhatsapp($notifiable)
    {
        if (!$notifiable->phone) {
            return;
        }

        $service = app(WhatsAppService::class);
        $message = "⚠️ *SOP OVERDUE ALERT*\n\n" .
            "Checklist: *{$this->checklist->name}*\n" .
            "Assigned Staff: *{$this->staffName}*\n" .
            "Deadline: " . date('g:i A', strtotime($this->checklist->deadline_time ?? '')) . "\n\n" .
            "Status: *MISSING / INCOMPLETE*\n\n" .
            "Please check the Kitchen OS dashboard.";

        return $service->sendMessage($notifiable->phone, $message);
    }
}
