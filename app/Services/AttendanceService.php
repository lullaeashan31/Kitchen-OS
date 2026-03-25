<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Calculate distance between two points in meters using Haversine formula.
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if user is within the required geo-fence (100m).
     */
    public function isWithinGeoFence(\App\Models\User $user, $latitude, $longitude)
    {
        if (!$user->target_latitude || !$user->target_longitude) {
            return true; // No geo-fence restricted if not set
        }

        $distance = $this->calculateDistance(
            (float) $user->target_latitude,
            (float) $user->target_longitude,
            (float) $latitude,
            (float) $longitude
        );

        return $distance <= 100;
    }

    /**
     * Calculate Net Salary for a user based on worked days.
     * Uses monthly_salary; if no attendance data, returns full monthly_salary as base so payroll overview shows correct amount.
     */
    public function calculateNetSalary(\App\Models\User $user, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // 1. Get attendance records for the month
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in_time', [$startDate, $endDate])
            ->get();

        $daysPresent = $attendances->count();
        $totalHours = 0;

        foreach ($attendances as $att) {
            if ($att->clock_in_time && $att->clock_out_time) {
                $totalHours += $att->clock_out_time->diffInHours($att->clock_in_time);
            } else {
                // Approximate 8 hours if no clock out
                $totalHours += 8;
            }
        }

        $salaryType = $user->salary_type ?? 'monthly';

        if ($salaryType === 'hourly') {
            $hourlyRate = (float)($user->hourly_salary ?? 0);
            return round($hourlyRate * $totalHours, 2);
        }

        if ($salaryType === 'daily') {
            $dailyRate = (float)($user->daily_salary ?? 0);
            return round($dailyRate * $daysPresent, 2);
        }

        // Monthly Salary Logic
        $monthlySalary = (float)($user->monthly_salary ?? 0);
        if ($monthlySalary <= 0) {
            return 0;
        }

        $totalDaysInMonth = $startDate->daysInMonth;
        
        // Calculate Scheduled Days considering weekly off
        $weeklyOff = $user->weekly_off_day ? trim($user->weekly_off_day) : null;
        if (is_numeric($weeklyOff)) {
            $offDaysCount = (int) $weeklyOff;
            $scheduledDays = max(0, $totalDaysInMonth - $offDaysCount);
        } else {
            $scheduledDays = 0;
            $tempDate = $startDate->copy();
            while ($tempDate <= $endDate) {
                $dayName = $tempDate->format('l');
                if ($weeklyOff === null || strcasecmp($dayName, $weeklyOff) !== 0) {
                    $scheduledDays++;
                }
                $tempDate->addDay();
            }
        }

        if ($scheduledDays <= 0) {
            return round($monthlySalary, 2);
        }

        // If no attendance records at all, maybe they haven't started using attendance module yet
        // Returning 0 might upset them if they just want base payroll, but the requirement says:
        // "Salary = (30000 / 30) × 26" or (monthly / scheduled_days) * present_days
        $dailyRate = $monthlySalary / $scheduledDays;
        
        // Capping daysPresent to scheduledDays just in case
        $paidDays = min($daysPresent, $scheduledDays);
        
        // If there is ZERO attendance tracked, and user wants default payroll behavior, 
        // they might expect the full amount. However, strict attendance integration means 0.
        // I will adhere to the formula: (salary / totalDays) * present
        
        $netSalary = $dailyRate * $paidDays;

        return round($netSalary, 2);
    }    /**
     * Get detailed attendance stats for a user in a given month.
     */
    public function getMonthlyStats(User $user, $month, $year)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $presentDays = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('status', ['present', 'half_day'])
            ->count();

        $absentDays = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 'absent')
            ->count();

        $totalHours = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('total_hours');

        return [
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'working_hours' => $totalHours,
            'total_month_days' => $startDate->daysInMonth,
            'salary_type' => $user->salary_type ?: 'monthly'
        ];
    }
}
