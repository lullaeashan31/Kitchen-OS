<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $pendingLeaves = LeaveRequest::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $historyLeaves = LeaveRequest::with(['user', 'approver'])
            ->where('status', '!=', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.leave.index', compact('pendingLeaves', 'historyLeaves'));
    }

    public function approve(Request $request, string $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, string $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Leave request rejected.');
    }
}
