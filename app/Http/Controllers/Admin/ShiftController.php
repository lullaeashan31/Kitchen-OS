<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shifts = \App\Models\Shift::all();
        return view('admin.shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'boolean',
        ]);

        \App\Models\Shift::create($validated);

        return redirect()->route('admin.shifts.index')->with('success', 'Shift created successfully.');
    }

    public function edit(\App\Models\Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, \App\Models\Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'boolean',
        ]);

        $shift->update($validated);

        return redirect()->route('admin.shifts.index')->with('success', 'Shift updated successfully.');
    }

    public function destroy(\App\Models\Shift $shift)
    {
        $shift->delete();
        return redirect()->route('admin.shifts.index')->with('success', 'Shift deleted successfully.');
    }
}
