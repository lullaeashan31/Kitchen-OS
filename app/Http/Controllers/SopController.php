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

        // 1. Get user's shift IDs assigned for today
        $assignments = $user->shiftAssignments()->where('date', $today)->pluck('shift_id');

        // Staff see checklists for their assigned shifts today, 
        // OR checklists for their role if they have no explicit shift assignment today.
        $checklists = SopChecklist::active()
            ->where('role', $user->role->value)
            ->when($assignments->isNotEmpty(), function ($q) use ($assignments) {
                $q->where(function ($sq) use ($assignments) {
                    $sq->whereIn('shift_id', $assignments)
                        ->orWhereNull('shift_id');
                });
            })
            ->with(['todayRun'])
            ->get();

        return view('sop.index', compact('checklists'));
    }

    public function execute(SopChecklist $checklist)
    {
        $user = Auth::user();
        $today = today();

        // 1. Verify access via shift assignment or role fallback
        $assignments = $user->shiftAssignments()->where('date', $today)->pluck('shift_id');

        $hasAccess = false;
        if ($user->isAdmin()) {
            $hasAccess = true;
        } elseif ($checklist->role === $user->role->value) {
            // Allow if it's a global SOP, or user is assigned to this shift, 
            // or user has no shifts assigned today (fallback)
            if ($assignments->isEmpty()) {
                $hasAccess = true;
            } else {
                $hasAccess = is_null($checklist->shift_id) || $assignments->contains($checklist->shift_id);
            }
        }

        if (!$hasAccess) {
            return redirect()->route('sop.index')->with('error', 'You do not have access to this checklist.');
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

            // Try S3 first
            try {
                $photoPath = $request->file('photo')->store($path, 's3');
                if (!$photoPath || $photoPath === '0' || $photoPath === 'false') {
                    throw new \Exception("S3 store failed.");
                }
                \Log::info("SOP Photo uploaded to S3: " . $photoPath);
            } catch (\Exception $e) {
                \Log::warning("SOP Photo S3 Upload Failed (falling back to public): " . $e->getMessage());
                // Fallback to public
                $photoPath = $request->file('photo')->store($path, 'public');
                if ($photoPath && $photoPath !== '0' && $photoPath !== 'false') {
                    \Log::info("SOP Photo uploaded to Public: " . $photoPath);
                } else {
                    \Log::error("SOP Photo Public Storage ALSO failed.");
                    $photoPath = null;
                }
            }
        } else {
            \Log::info("No photo in request for item " . $itemId . " (is_photo_required: " . ($item->is_photo_required ? 'true' : 'false') . ")");
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
