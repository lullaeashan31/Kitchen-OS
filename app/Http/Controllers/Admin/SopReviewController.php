<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SopDailyRun;
use App\Models\SopItemCompletion;
use App\Models\User;
use App\Notifications\SopItemRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SopReviewController extends Controller
{
    public function index(string $kitchen_slug)
    {
        $runs = SopDailyRun::with(['checklist', 'user', 'completions'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('admin.sop.review_index', compact('runs'));
    }

    public function show(string $kitchen_slug, SopDailyRun $run)
    {
        $run->load(['checklist.items', 'completions.item', 'user']);

        $completions = $run->completions->keyBy('item_id');

        return view('admin.sop.review_run', compact('run', 'completions'));
    }

    public function approveRun(string $kitchen_slug, SopDailyRun $run)
    {
        $run->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'SOP Run approved successfully.');
    }

    public function approveItem(string $kitchen_slug, SopDailyRun $run, SopItemCompletion $completion)
    {
        $completion->update([
            'status' => 'completed',
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Item approved.');
    }

    public function rejectItem(Request $request, string $kitchen_slug, SopDailyRun $run, SopItemCompletion $completion)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $completion->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        // Notify the staff member who did the run
        $run->user->notify(new SopItemRejected($completion));

        // Update run status back to pending since it's no longer fully approved/done if an item is rejected
        $run->update(['status' => 'pending', 'approved_at' => null]);

        return back()->with('success', 'Item rejected and staff notified.');
    }
}
