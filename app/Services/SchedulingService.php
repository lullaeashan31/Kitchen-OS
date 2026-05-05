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
     * Generate schedule for a given date range with fair rotation.
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
            $shifts = Shift::where('is_active', true)->orderBy('start_time')->get();
            $staff = User::where('role', \App\Enums\UserRole::Staff)
                ->where('onboarding_status', 'active')
                ->get();

            if ($staff->isEmpty()) return;

            // Keep a local assignment counter map for this run.
            // (Collection::fill does not exist on Support\Collection.)
            $staffAssignedCounts = array_fill_keys($staff->pluck('id')->toArray(), 0);
            $staffIds = $staff->pluck('id')->toArray();
            shuffle($staffIds); // Randomize initial order for fairness over weeks

            $currentDate = $start->copy();
            while ($currentDate <= $end) {
                foreach ($shifts as $shift) {
                    $required = (int)($shift->required_staff ?? 1);

                    // 1. Get available staff for this date (considering leaves/off-days)
                    $availableOnDate = $this->getAvailableStaffForDate($staff, $currentDate);

                    // 2. Filter out already assigned today
                    $alreadyAssignedToday = ShiftAssignment::where('date', $currentDate->toDateString())
                        ->pluck('user_id')
                        ->toArray();

                    $eligible = $availableOnDate->filter(fn($s) => !in_array($s->id, $alreadyAssignedToday));

                    // 3. To rotate fairly, sort eligible staff by their assignment count (asc)
                    // Optimization: Bulk fetch assignment counts to avoid N+1 queries
                    $eligibleIds = $eligible->pluck('id')->toArray();
                    $historicalCounts = ShiftAssignment::whereIn('user_id', $eligibleIds)
                        ->select('user_id', DB::raw('count(*) as count'))
                        ->groupBy('user_id')
                        ->pluck('count', 'user_id')
                        ->toArray();

                    $eligible = $eligible->sortBy(function ($user) use ($historicalCounts, $staffAssignedCounts) {
                        $history = $historicalCounts[$user->id] ?? 0;
                        $currentRun = $staffAssignedCounts[$user->id] ?? 0;
                        return $history + $currentRun;
                    });

                    // 4. Assign up to 'required' count
                    $toAssign = $eligible->take($required);

                    foreach ($toAssign as $user) {
                        ShiftAssignment::updateOrCreate(
                            ['user_id' => $user->id, 'date' => $currentDate->toDateString()],
                            ['shift_id' => $shift->id]
                        );
                        $staffAssignedCounts[$user->id] = ($staffAssignedCounts[$user->id] ?? 0) + 1;
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
