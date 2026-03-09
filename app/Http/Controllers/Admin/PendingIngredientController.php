<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class PendingIngredientController extends Controller
{
    public function index(string $kitchen_slug)
    {
        $pendingIngredients = Ingredient::where('status', 'pending')->latest()->get();
        return view('admin.ingredients.pending', compact('pendingIngredients'));
    }

    public function update(Request $request, string $kitchen_slug, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'purchase_unit' => 'required|string',
            'alert_threshold' => 'nullable|numeric|min:0',
            'vendor' => 'nullable|string',
        ]);

        $validated['status'] = 'approved';
        $ingredient->update($validated);

        return redirect()->back()->with('success', 'Ingredient approved and finalized.');
    }
}
