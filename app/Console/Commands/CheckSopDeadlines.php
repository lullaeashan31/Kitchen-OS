<?php

namespace App\Console\Commands;

use App\Models\SopChecklist;
use App\Models\SopDailyRun;
use App\Models\SopAlertLog;
use App\Models\User;
use App\Notifications\SopDeadlineMissed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckSopDeadlines extends Command
{
    protected $signature = 'sop:check-deadlines';
    protected $description = 'Check for missed SOP deadlines and trigger alerts';

    public function handle()
    {
        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();

        // 1. Find active checklists whose deadline has passed today
        $overdueChecklists = SopChecklist::active()
            ->where('deadline_time', '<', $currentTime)
            ->with([
                'shift.assignments' => function ($q) use ($today) {
                    $q->where('date', $today)->with('user');
                }
            ])
            ->get();

        /** @var \App\Models\SopChecklist $checklist */
        foreach ($overdueChecklists as $checklist) {
            // 2. Check if already completed and approved for today
            $run = SopDailyRun::where('checklist_id', $checklist->id)
                ->where('date', $today)
                ->where('status', 'approved')
                ->exists();

            if (!$run) {
                // 3. Check if we already sent an alert for this checklist today
                $alertExists = SopAlertLog::where('checklist_id', $checklist->id)
                    ->where('date', $today)
                    ->where('alert_type', 'deadline_missed')
                    ->exists();

                if (!$alertExists) {
                    // 4. Find assigned staff for this shift today
                    $staffName = 'Unassigned';
                    if ($checklist->shift) {
                        $assignment = $checklist->shift->assignments->first();
                        if ($assignment && $assignment->user) {
                            $staffName = $assignment->user->name;
                        }
                    }

                    // 4. Trigger Notifications for Managers and Admins
                    $notifiables = User::whereIn('role', ['manager', 'admin'])->get();
                    Notification::send($notifiables, new SopDeadlineMissed($checklist, $staffName));

                    // 5. Log the alert
                    SopAlertLog::create([
                        'checklist_id' => $checklist->id,
                        'date' => $today,
                        'alert_type' => 'deadline_missed',
                        'status' => 'sent',
                        'staff_name' => $staffName // I should probably add this to the table if I want to persist it, but for now passing it to notification is key.
                    ]);

                    $this->info("Alert sent for checklist: {$checklist->name} (Staff: {$staffName})");
                }
            }
        }

        $this->info('Deadline check completed.');
    }
}
