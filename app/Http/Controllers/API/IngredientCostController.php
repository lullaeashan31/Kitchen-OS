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
            // Convert input unit to ingredient's base unit (measurement_unit)
            // Note: Ingredient model has 'measurement_unit' which is the base for price.
            // Assumption: price is per 'measurement_unit' (or 'purchase_unit' converted to base).
            // Usually 'price' is "Price per Unit". 
            // If I buy 1kg for $10, price is 10, unit is kg.
            // If I recipe uses 500g.
            // I need to convert 500g -> 0.5kg.
            // Then 0.5 * 10 = $5.

            // So: Convert (qty, from: input_unit, to: ingredient_unit)
            $quantityInBase = $this->unitService->convert(
                $request->quantity,
                $request->unit,
                $ingredient->measurement_unit
            );

            $cost = $quantityInBase * $ingredient->price;

            return response()->json(['cost' => round($cost, 3)]);

        } catch (\Exception $e) {
            // Fallback or error if conversion fails (e.g. incompatible units)
            // For now return 0 or error?
            // Let's return error so frontend knows.
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
