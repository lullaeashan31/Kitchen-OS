<?php

namespace App\Http\Controllers;

use App\Models\SopChecklist;
use App\Models\SopLog;
use App\Models\SopItemLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SopController extends Controller
{
    public function index()
    {
        // Get all checklists with today's status
        $checklists = SopChecklist::with('todayLog')->get();
        return view('sop.index', compact('checklists'));
    }

    public function execute(SopChecklist $checklist)
    {
        // Find or Create Log for Today
        $log = SopLog::firstOrCreate(
            ['checklist_id' => $checklist->id, 'log_date' => today()],
            ['user_id' => Auth::id(), 'status' => 'pending']
        );

        // Load item logs
        $items = $checklist->items;
        $completedItemIds = $log->itemLogs->pluck('item_id')->toArray();
        $itemLogs = $log->itemLogs->keyBy('item_id');

        return view('sop.execute', compact('checklist', 'log', 'items', 'itemLogs'));
    }

    public function updateItem(Request $request, SopChecklist $checklist, $itemId)
    {
        $log = SopLog::where('checklist_id', $checklist->id)->where('log_date', today())->firstOrFail();

        $request->validate([
            'photo' => 'required|image|max:10240', // 10MB
        ]);

        $photoPath = $request->file('photo')->store('sop_photos/' . date('Y-m-d'), 'public');

        SopItemLog::updateOrCreate(
            ['log_id' => $log->id, 'item_id' => $itemId],
            [
                'is_completed' => true,
                'photo_path' => $photoPath,
                'completed_at' => now()
            ]
        );

        // Check if all items completed
        $totalItems = $checklist->items()->count();
        $completedItems = $log->itemLogs()->where('is_completed', true)->count();

        if ($completedItems >= $totalItems) {
            $log->update(['status' => 'completed', 'completed_at' => now()]);
            return back()->with('success', 'Item checked! Checklist COMPLETE.');
        }

        return back()->with('success', 'Item checked & photo uploaded.');
    }

    public function report(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        // Fetch all logs for date
        $logs = SopLog::with(['checklist', 'itemLogs.item', 'user'])
            ->whereDate('log_date', $date)
            ->get();

        return view('sop.report', compact('logs', 'date'));
    }
}
