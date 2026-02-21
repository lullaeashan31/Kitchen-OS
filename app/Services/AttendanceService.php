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
        $monthlySalary = (float) ($user->monthly_salary ?? 0);
        if ($monthlySalary <= 0) {
            return 0;
        }

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $weeklyOff = $user->weekly_off_day ? trim($user->weekly_off_day) : null;

        // Count scheduled working days (exclude weekly off)
        $scheduledDays = 0;
        $tempDate = $startDate->copy();
        while ($tempDate <= $endDate) {
            $dayName = $tempDate->format('l');
            if ($weeklyOff === null || strcasecmp($dayName, $weeklyOff) !== 0) {
                $scheduledDays++;
            }
            $tempDate->addDay();
        }

        if ($scheduledDays <= 0) {
            return round($monthlySalary, 2);
        }

        $daysWorked = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in_time', [$startDate, $endDate])
            ->count();

        // If no attendance records, use full monthly salary as base (so payroll overview shows correct amount)
        if ($daysWorked <= 0) {
            return round($monthlySalary, 2);
        }

        $dailyRate = $monthlySalary / $scheduledDays;
        $netSalary = $dailyRate * $daysWorked;

        return round($netSalary, 2);
    }
}
