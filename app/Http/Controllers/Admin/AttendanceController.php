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
    public function index(Request $request, string $kitchen_slug)
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
    public function update(Request $request, string $kitchen_slug, string $id)
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

    /**
     * Force clock out an active session.
     */
    public function forceClockOut(string $kitchen_slug, string $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $attendance = Attendance::findOrFail($id);

        if ($attendance->clock_out_time) {
            return redirect()->back()->with('error', 'Session already closed.');
        }

        $attendance->update([
            'clock_out_time' => now(),
            'status' => 'success', // Assume success if admin closes it
        ]);

        return redirect()->back()->with('success', 'Staff member clocked out manually.');
    }
}
