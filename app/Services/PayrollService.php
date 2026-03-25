<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\PerformanceReview;
use App\Models\PayrollRecord;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollService
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Generate payroll for all staff for a given month and year.
     */
    public function generatePayroll($month, $year)
    {
        $staff = User::where('role', UserRole::Staff)->where('onboarding_status', 'active')->get();
        $results = [
            'total_processed' => 0,
            'errors' => []
        ];

        foreach ($staff as $member) {
            try {
                DB::transaction(function () use ($member, $month, $year, &$results) {
                    $user = $member instanceof User ? $member : User::find($member->id);
                    if (!$user) {
                        return;
                    }
                    // Use fresh data from DB so latest monthly_salary is used
                    $user->refresh();

                    // 1. Calculate Base Salary
                    $baseSalary = $this->attendanceService->calculateNetSalary($user, $month, $year);

                    // 1.1 Get Stats
                    $stats = $this->attendanceService->getMonthlyStats($user, $month, $year);

                    // 2. Fetch Performance Bonus
                    $review = PerformanceReview::where('user_id', $user->id)
                        ->where('month', $month)
                        ->where('year', $year)
                        ->first();

                    $bonus = $review ? $review->bonus_amount : 0;

                    // 3. Create or Update Payroll Record
                    PayrollRecord::updateOrCreate(
                        ['user_id' => $user->id, 'month' => $month, 'year' => $year],
                        [
                            'salary_type' => $stats['salary_type'],
                            'present_days' => $stats['present_days'],
                            'absent_days' => $stats['absent_days'],
                            'working_hours' => $stats['working_hours'],
                            'base_salary' => $baseSalary,
                            'bonus' => $bonus,
                            'net_salary' => $baseSalary + $bonus,
                            'status' => 'pending'
                        ]
                    );

                    $results['total_processed']++;
                });
            } catch (\Exception $e) {
                $results['errors'][] = "Error processing {$member->name}: " . $e->getMessage();
            }
        }

        return $results;
    }
}
