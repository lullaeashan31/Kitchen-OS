<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShiftAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $shifts = \App\Models\Shift::where('is_active', true)->get();
        $users = \App\Models\User::where('role', 'staff')->get();

        $assignments = \App\Models\ShiftAssignment::where('date', $date)
            ->with(['user', 'shift'])
            ->get()
            ->groupBy('shift_id');

        return view('admin.shifts.assignments', compact('shifts', 'users', 'date', 'assignments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'assignments' => 'required|array', // shift_id => user_id
        ]);

        foreach ($request->assignments as $shiftId => $userId) {
            if ($userId) {
                \App\Models\ShiftAssignment::updateOrCreate(
                    ['date' => $request->date, 'shift_id' => $shiftId],
                    ['user_id' => $userId]
                );
            } else {
                \App\Models\ShiftAssignment::where('date', $request->date)
                    ->where('shift_id', $shiftId)
                    ->delete();
            }
        }

        return back()->with('success', 'Assignments updated successfully.');
    }
}
