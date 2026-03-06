<?php

namespace App\Services;

use App\Models\User;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SchedulingService
{
    /**
     * Generate schedule for a given date range.
     */
    public function generateSchedule($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $results = [
            'total_assigned' => 0,
            'understaffed_days' => [],
        ];

        DB::transaction(function () use ($start, $end, &$results) {
            $currentDate = $start->copy();
            $shifts = Shift::where('is_active', true)->get();
            $staff = User::where('role', 'staff')
                ->where('onboarding_status', 'active')
                ->get();

            while ($currentDate <= $end) {
                foreach ($shifts as $shift) {
                    $required = $shift->required_staff ?? 1; // Fallback to 1

                    // 1. Find available staff
                    $availableStaff = $this->getAvailableStaffForDate($staff, $currentDate);

                    // 2. Filter out those already assigned on this date
                    $alreadyAssignedIds = ShiftAssignment::where('date', $currentDate->toDateString())
                        ->pluck('user_id')
                        ->toArray();

                    $eligibleStaff = $availableStaff->filter(function ($s) use ($alreadyAssignedIds) {
                        return !in_array($s->id, $alreadyAssignedIds);
                    });

                    // 3. Assign up to 'required' count
                    $toAssign = $eligibleStaff->take($required);

                    foreach ($toAssign as $user) {
                        ShiftAssignment::updateOrCreate(
                            ['user_id' => $user->id, 'date' => $currentDate->toDateString()],
                            ['shift_id' => $shift->id]
                        );
                        $results['total_assigned']++;
                    }

                    if ($toAssign->count() < $required) {
                        $results['understaffed_days'][] = [
                            'date' => $currentDate->toDateString(),
                            'shift' => $shift->name,
                            'missing' => $required - $toAssign->count()
                        ];
                    }
                }
                $currentDate->addDay();
            }
        });

        return $results;
    }

    /**
     * Check if staff is available on a specific date.
     */
    private function getAvailableStaffForDate($staff, $date)
    {
        $dayName = $date->format('l');
        return $staff->filter(function ($user) use ($date, $dayName) {
            // 1. Check Weekly Off (Only for legacy string values)
            if (!is_numeric($user->weekly_off_day)) {
                if ($user->weekly_off_day === $dayName) {
                    return false;
                }
            }

            // 2. Check Approved Leave
            $hasLeave = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();

            if ($hasLeave) {
                return false;
            }

            return true;
        });
    }
}
