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
            'purchase_price' => 'required|numeric|min:0',
            'purchase_quantity' => 'required|numeric|min:0.001',
            'purchase_unit' => 'required|string',
            'alert_threshold' => 'nullable|numeric|min:0',
            'vendor' => 'nullable|string',
        ]);

        $validated['price'] = $validated['purchase_price'];
        $validated['status'] = 'approved';
        $ingredient->update($validated);

        return redirect()->route('admin.ingredients.pending', ['kitchen_slug' => $kitchen_slug])
            ->with('success', 'Ingredient "' . $ingredient->name . '" approved and ready for use.');
    }

    public function destroy(string $kitchen_slug, Ingredient $ingredient)
    {
        $ingredient->delete();
        return redirect()->route('admin.ingredients.pending', ['kitchen_slug' => $kitchen_slug])
            ->with('info', 'Pending ingredient rejected and discarded.');
    }
}
