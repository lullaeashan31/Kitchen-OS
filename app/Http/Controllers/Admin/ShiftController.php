<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shifts = \App\Models\Shift::all();
        return view('admin.shifts.index', compact('shifts'));
    }

    public function create()
    {
        $existingShifts = \App\Models\Shift::orderBy('name')->get();
        return view('admin.shifts.create', compact('existingShifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'required_staff' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        \App\Models\Shift::create($validated);

        return redirect()->route('admin.shifts.index')->with('success', 'Shift created successfully.');
    }

    public function edit(\App\Models\Shift $shift)
    {
        $existingShifts = \App\Models\Shift::orderBy('name')->get();
        return view('admin.shifts.edit', compact('shift', 'existingShifts'));
    }

    public function update(Request $request, \App\Models\Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'required_staff' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $shift->update($validated);

        return redirect()->route('admin.shifts.index')->with('success', 'Shift updated successfully.');
    }

    public function destroy(\App\Models\Shift $shift)
    {
        $shift->delete();
        return redirect()->route('admin.shifts.index')->with('success', 'Shift deleted successfully.');
    }

    /**
     * Trigger auto-scheduling logic for the next week.
     */
    public function autoGenerate(\App\Services\SchedulingService $schedulingService)
    {
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(8)->endOfDay();

        $results = $schedulingService->generateSchedule($startDate, $endDate);

        $message = "Successfully assigned {$results['total_assigned']} shifts.";
        if (!empty($results['understaffed_days'])) {
            $message .= " Note: " . count($results['understaffed_days']) . " shifts could not be fully staffed.";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Display the employee's own schedule.
     */
    public function mySchedule()
    {
        $startDate = today();
        $endDate = today()->addDays(6);

        $assignments = ScheduleAssignment::with('role')
            ->where('user_id', auth()->id())
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));

        return view('employee.shifts.index', compact('assignments'));
    }
}
