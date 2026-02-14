<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SopChecklist;
use App\Models\SopChecklistItem;
use App\Models\SopChecklistAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SopController extends Controller
{
    public function index()
    {
        $checklists = SopChecklist::with(['shift'])->withCount('items')->get();
        return view('admin.sop.index', compact('checklists'));
    }

    public function create()
    {
        $shifts = \App\Models\Shift::where('is_active', true)->get();
        return view('admin.sop.create', compact('shifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'shift_id' => 'required|exists:shifts,id',
            'role' => 'required|string',
            'deadline_time' => 'required',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.is_photo_required' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            $checklist = SopChecklist::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'shift_id' => $validated['shift_id'],
                'role' => $validated['role'],
                'deadline_time' => $validated['deadline_time'],
                'status' => 'active',
            ]);

            // Add items
            foreach ($validated['items'] as $index => $item) {
                $checklist->items()->create([
                    'name' => $item['name'],
                    'is_photo_required' => $item['is_photo_required'] ?? false,
                    'sort_order' => $index,
                ]);
            }
        });

        return redirect()->route('admin.sop.index')->with('success', 'SOP Checklist created successfully.');
    }

    public function edit(SopChecklist $sop)
    {
        $sop->load(['items', 'shift']);
        $shifts = \App\Models\Shift::where('is_active', true)->get();
        return view('admin.sop.edit', compact('sop', 'shifts'));
    }

    public function update(Request $request, SopChecklist $sop)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'shift_id' => 'required|exists:shifts,id',
            'role' => 'required|string',
            'deadline_time' => 'required',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:sop_checklist_items,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.is_photo_required' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $sop) {
            $sop->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'shift_id' => $validated['shift_id'],
                'role' => $validated['role'],
                'deadline_time' => $validated['deadline_time'],
            ]);

            // Simple item sync for now (delete missing, update existing, create new)
            $itemIds = collect($validated['items'])->pluck('id')->filter()->toArray();
            $sop->items()->whereNotIn('id', $itemIds)->delete();

            foreach ($validated['items'] as $index => $itemData) {
                if (isset($itemData['id'])) {
                    SopChecklistItem::find($itemData['id'])->update([
                        'name' => $itemData['name'],
                        'is_photo_required' => $itemData['is_photo_required'] ?? false,
                        'sort_order' => $index,
                    ]);
                } else {
                    $sop->items()->create([
                        'name' => $itemData['name'],
                        'is_photo_required' => $itemData['is_photo_required'] ?? false,
                        'sort_order' => $index,
                    ]);
                }
            }
        });

        return redirect()->route('admin.sop.index')->with('success', 'SOP Checklist updated successfully.');
    }

    public function archive(SopChecklist $checklist)
    {
        $checklist->update(['status' => 'archived']);
        return back()->with('success', 'Checklist archived.');
    }

    public function pause(SopChecklist $checklist)
    {
        $newStatus = $checklist->status === 'paused' ? 'active' : 'paused';
        $checklist->update(['status' => $newStatus]);
        return back()->with('success', 'Checklist status updated to ' . $newStatus);
    }

    public function reorder(Request $request)
    {
        $order = $request->input('order'); // Array of IDs
        foreach ($order as $index => $id) {
            SopChecklistItem::where('id', $id)->update(['sort_order' => $index]);
        }
        return response()->json(['success' => true]);
    }
}
