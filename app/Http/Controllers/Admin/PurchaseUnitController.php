<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseUnitController extends Controller
{
    public function index()
    {
        $units = PurchaseUnit::orderBy('name')->get();
        return view('admin.purchase_units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.purchase_units.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'base_unit' => 'required|string|max:20',
            'conversion_factor' => 'required|numeric|min:0.0001',
        ]);

        PurchaseUnit::create([
            'kitchen_id' => Auth::user()->kitchen_id,
            'name' => $request->name,
            'base_unit' => $request->base_unit,
            'conversion_factor' => $request->conversion_factor,
        ]);

        return redirect()->route('admin.purchase-units.index', ['kitchen_slug' => Auth::user()->kitchen->slug])
            ->with('success', 'Purchase unit created successfully.');
    }

    public function edit(string $kitchen_slug, PurchaseUnit $purchase_unit)
    {
        return view('admin.purchase_units.edit', ['unit' => $purchase_unit]);
    }

    public function update(Request $request, string $kitchen_slug, PurchaseUnit $purchase_unit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'base_unit' => 'required|string|max:20',
            'conversion_factor' => 'required|numeric|min:0.0001',
        ]);

        $purchase_unit->update([
            'name' => $request->name,
            'base_unit' => $request->base_unit,
            'conversion_factor' => $request->conversion_factor,
        ]);

        return redirect()->route('admin.purchase-units.index', ['kitchen_slug' => $kitchen_slug])
            ->with('success', 'Purchase unit updated successfully.');
    }

    public function destroy(string $kitchen_slug, PurchaseUnit $purchase_unit)
    {
        $purchase_unit->delete();
        return redirect()->route('admin.purchase-units.index', ['kitchen_slug' => $kitchen_slug])
            ->with('success', 'Purchase unit deleted successfully.');
    }
}
