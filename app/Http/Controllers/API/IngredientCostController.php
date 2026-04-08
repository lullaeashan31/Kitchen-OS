<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Services\UnitConversionService;
use Illuminate\Http\Request;

class IngredientCostController extends Controller
{
    protected $unitService;

    public function __construct(UnitConversionService $unitService)
    {
        $this->unitService = $unitService;
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string',
        ]);

        $ingredient = Ingredient::findOrFail($request->ingredient_id);

        // If price is 0 (e.g. pending), return 0
        if ($ingredient->price <= 0) {
            return response()->json(['cost' => 0.00]);
        }

        try {
            // Simplified: No unit conversion
            $cost = (float)$request->quantity * (float)$ingredient->latest_price;

            return response()->json(['cost' => round($cost, 3)]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
