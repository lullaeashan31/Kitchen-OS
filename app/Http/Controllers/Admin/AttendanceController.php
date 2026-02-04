<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        $attendances = Attendance::with('user')
            ->whereDate('clock_in_time', $date)
            ->orderBy('clock_in_time', 'desc')
            ->get();

        return view('admin.attendance.index', compact('attendances', 'date'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:success,rejected',
        ]);

        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Attendance updated successfully.');
    }
}
