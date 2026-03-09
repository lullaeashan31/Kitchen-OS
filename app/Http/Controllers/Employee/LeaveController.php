<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index(string $kitchen_slug)
    {
        $leaves = LeaveRequest::where('user_id', auth()->id())
            ->orderBy('start_date', 'desc')
            ->get();

        return view('employee.leave.index', compact('leaves'));
    }

    public function create(string $kitchen_slug)
    {
        return view('employee.leave.create');
    }

    public function store(Request $request, string $kitchen_slug)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:casual,sick,annual',
            'reason' => 'required|string|min:10',
        ]);

        $startDate = Carbon::parse($request->start_date);

        // 2-week advance rule (except for sick leave)
        if ($request->type !== 'sick') {
            if ($startDate->diffInDays(now()) < 14) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Non-emergency leave requests must be made at least 2 weeks in advance.');
            }
        }

        LeaveRequest::create([
            'user_id' => auth()->id(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $request->type,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('employee.leave.index')->with('success', 'Leave request submitted successfully.');
    }
}
