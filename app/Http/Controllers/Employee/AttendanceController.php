<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $user = auth()->user();

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in_time', [$startDate, $endDate])
            ->orderBy('clock_in_time', 'asc')
            ->get();

        // Calculate some stats
        $daysWorked = $attendances->count();
        $totalMinutes = 0;
        foreach ($attendances as $att) {
            if ($att->clock_out_time) {
                $totalMinutes += $att->clock_in_time->diffInMinutes($att->clock_out_time);
            }
        }
        $averageHours = $daysWorked > 0 ? round(($totalMinutes / 60) / $daysWorked, 1) : 0;

        return view('employee.attendance.index', compact('attendances', 'month', 'year', 'daysWorked', 'averageHours'));
    }
}
