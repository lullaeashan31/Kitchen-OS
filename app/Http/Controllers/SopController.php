<?php

namespace App\Http\Controllers;

use App\Models\SopChecklist;
use App\Models\SopDailyRun;
use App\Models\SopItemCompletion;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SopController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = today();

        // 1. Get user's shift assignment for today
        $assignments = $user->shiftAssignments()->where('date', $today)->pluck('shift_id');

        // Staff see checklists for their assigned shifts today
        $checklists = SopChecklist::active()
            ->whereIn('shift_id', $assignments)
            ->with(['todayRun'])
            ->get();

        return view('sop.index', compact('checklists'));
    }

    public function execute(SopChecklist $checklist)
    {
        $user = Auth::user();
        $today = today();

        // 1. Verify access via shift assignment
        $hasAccess = $user->shiftAssignments()
            ->where('date', $today)
            ->where('shift_id', $checklist->shift_id)
            ->exists();

        if (!$hasAccess && !$user->isAdmin()) {
            return redirect()->route('sop.index')->with('error', 'You are not assigned to this shift today.');
        }

        // 2. Find or Create Daily Run
        $run = SopDailyRun::firstOrCreate(
            ['checklist_id' => $checklist->id, 'date' => today()],
            ['user_id' => $user->id, 'status' => 'pending']
        );

        $checklist->load('items');
        $completions = $run->completions->keyBy('item_id');

        return view('sop.execute', compact('checklist', 'run', 'completions'));
    }

    public function updateItem(Request $request, SopChecklist $checklist, $itemId)
    {
        $user = Auth::user();
        $run = SopDailyRun::where('checklist_id', $checklist->id)->where('date', today())->firstOrFail();
        $item = $checklist->items()->findOrFail($itemId);

        $rules = [];
        if ($item->is_photo_required) {
            $rules['photo'] = 'required|image|max:10240'; // 10MB
        }

        $request->validate($rules);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $dateFolder = today()->toDateString();
            $path = "sop/photos/{$dateFolder}/{$checklist->id}";

            // Save to S3 as requested
            try {
                $photoPath = $request->file('photo')->store($path, 's3');
            } catch (\Exception $e) {
                \Log::error("SOP Photo S3 Upload Failed: " . $e->getMessage());
                // Fallback to public if s3 fails (to avoid blocking staff)
                $photoPath = $request->file('photo')->store($path, 'public');
            }
        }

        $completion = SopItemCompletion::where('run_id', $run->id)
            ->where('item_id', $itemId)
            ->first();

        $photoHistory = $completion ? ($completion->photo_history ?? []) : [];
        $status = 'completed';

        if ($completion && $completion->status === 'rejected') {
            // Move current photo to history
            if ($completion->photo_path) {
                $photoHistory[] = [
                    'path' => $completion->photo_path,
                    'rejected_at' => $completion->updated_at->toDateTimeString(),
                    'reason' => $completion->rejection_reason
                ];
            }
            $status = 'resubmitted';
        }

        $completion = SopItemCompletion::updateOrCreate(
            ['run_id' => $run->id, 'item_id' => $itemId],
            [
                'user_id' => $user->id,
                'is_completed' => true,
                'status' => $status,
                'photo_path' => $photoPath ?? ($completion ? $completion->photo_path : null),
                'photo_history' => $photoHistory,
                'completed_at' => now(),
                'rejection_reason' => null, // Reset reason on resubmit
            ]
        );

        if ($status === 'resubmitted') {
            // Notify managers of resubmission
            $managers = \App\Models\User::whereIn('role', ['manager', 'admin'])->get();
            \Illuminate\Support\Facades\Notification::send($managers, new \App\Notifications\SopItemResubmitted($completion));
        }

        return response()->json([
            'success' => true,
            'message' => 'Item completed!',
            'all_done' => $this->isChecklistFullyCompleted($checklist, $run)
        ]);
    }

    public function complete(SopChecklist $checklist)
    {
        $run = SopDailyRun::where('checklist_id', $checklist->id)->where('date', today())->firstOrFail();

        if ($this->isChecklistFullyCompleted($checklist, $run)) {
            $run->update([
                'status' => 'approved', // Auto-approve on completion
                'approved_at' => now(),
                'completed_at' => now()
            ]);

            // TODO: Notify Manager

            return redirect()->route('sop.index')->with('success', 'Checklist submitted and auto-approved!');
        }

        return back()->with('error', 'Please complete all items first.');
    }

    private function isChecklistFullyCompleted($checklist, $run)
    {
        $totalItems = $checklist->items()->count();
        $completedItems = $run->completions()->where('is_completed', true)->count();
        return $completedItems >= $totalItems;
    }

    public function report(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $runs = SopDailyRun::with(['checklist', 'completions.item', 'user'])
            ->whereDate('date', $date)
            ->get();

        return view('sop.report', compact('runs', 'date'));
    }
}
