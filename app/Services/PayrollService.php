<?php

namespace App\Services;

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
        $staff = User::where('role', 'staff')->where('onboarding_status', 'active')->get();
        $results = [
            'total_processed' => 0,
            'errors' => []
        ];

        foreach ($staff as $member) {
            try {
                DB::transaction(function () use ($member, $month, $year, &$results) {
                    if (!$member instanceof \App\Models\User) {
                        // Fallback in case of unexpected collection type
                        $member = \App\Models\User::find($member->id);
                    }

                    // 1. Calculate Base Salary from Attendance
                    $baseSalary = $this->attendanceService->calculateNetSalary($member, $month, $year);

                    // 2. Fetch Performance Bonus
                    $review = PerformanceReview::where('user_id', $member->id)
                        ->where('month', $month)
                        ->where('year', $year)
                        ->first();

                    $bonus = $review ? $review->bonus_amount : 0;

                    // 3. Create or Update Payroll Record
                    PayrollRecord::updateOrCreate(
                        ['user_id' => $member->id, 'month' => $month, 'year' => $year],
                        [
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
