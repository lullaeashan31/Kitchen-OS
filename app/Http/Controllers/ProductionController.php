<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\ProductionLog;
use App\Models\InventoryLog;
use App\Enums\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductionTemplateExport;
use App\Services\FIFOInventoryService;

class ProductionController extends Controller
{
    protected $fifoService;

    public function __construct(FIFOInventoryService $fifoService)
    {
        $this->fifoService = $fifoService;
    }

    /**
     * Show the production/cooking page.
     */
    public function create(string $kitchen_slug)
    {
        // Only show approved/permanent recipes that are SUB-RECIPES (produce an ingredient)
        // Show all approved/permanent recipes (both sub-recipes and main dishes)
        $recipes = Recipe::where('status', \App\Enums\RecipeStatus::Permanent)
            ->with(['producesIngredient']) // Eager load if applicable
            ->orderBy('name')
            ->get();

        return view('production.create', compact('recipes'));
    }

    /**
     * Record a cooking session (deduct stock).
     */
    public function store(Request $request, string $kitchen_slug)
    {
        $validated = $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'quantity' => 'required|numeric|min:0.1',
            'unit_type' => 'required|in:batches,portions',
        ]);

        // Load recipe with all ingredients from all stages
        $recipe = Recipe::with(['stages.ingredients.ingredient'])->findOrFail($validated['recipe_id']);

        // Collect all ingredients from all stages, grouping by ingredient_id and summing quantities
        $ingredientsMap = [];
        foreach ($recipe->stages as $stage) {
            foreach ($stage->ingredients as $recipeIngredient) {
                $ingredient = $recipeIngredient->ingredient;
                if ($ingredient) {
                    $ingredientId = $ingredient->id;

                    if (!isset($ingredientsMap[$ingredientId])) {
                        // First occurrence - create pivot object
                        $ingredient->pivot = (object) [
                            'quantity' => $recipeIngredient->quantity,
                            'unit' => $recipeIngredient->unit,
                            'cost' => $recipeIngredient->cost,
                            'ingredient_group' => $recipeIngredient->ingredient_group,
                            'recipe_stage_id' => $recipeIngredient->recipe_stage_id,
                        ];
                        $ingredientsMap[$ingredientId] = $ingredient;
                    } else {
                        // Duplicate ingredient - sum quantities
                        $ingredientsMap[$ingredientId]->pivot->quantity += $recipeIngredient->quantity;
                    }
                }
            }
        }

        // Replace the ingredients relation with our collected ingredients
        $recipe->setRelation('ingredients', collect(array_values($ingredientsMap)));

        // Calculate total portions
        $portions = $validated['quantity'];
        $yieldValue = $recipe->yield_portions ?? $recipe->yields ?? 1;
        if ($validated['unit_type'] === 'batches') {
            $portions = $validated['quantity'] * $yieldValue;
        }

        try {
        DB::transaction(function () use ($recipe, $portions, $request, $yieldValue) {
            // Check Stock Availability Inside Transaction (with lock to prevent race conditions)
            $missingStock = [];
            foreach ($recipe->ingredients as $ingredient) {
                $lockedIngredient = \App\Models\Ingredient::lockForUpdate()->findOrFail($ingredient->id);
                $requiredQtyInRecipeUnit = ($ingredient->pivot->quantity * $portions) / $yieldValue;

                $recipeUnit = Unit::tryFrom($ingredient->pivot->unit);
                $stockUnit = Unit::tryFrom($lockedIngredient->measurement_unit);

                if ($recipeUnit && $stockUnit && $recipeUnit->canConvertTo($stockUnit)) {
                    $requiredQty = $recipeUnit->convertTo($requiredQtyInRecipeUnit, $stockUnit);
                } else {
                    $requiredQty = $requiredQtyInRecipeUnit;
                }

                if ($lockedIngredient->current_stock < $requiredQty) {
                    throw new \Exception('Insufficient stock for ' . $lockedIngredient->name . ' (Need: ' . number_format($requiredQty, 3) . ' ' . $lockedIngredient->measurement_unit . ', Have: ' . number_format($lockedIngredient->current_stock, 3) . ')');
                }
            }
            // 1. Initialize costs and create placeholder log
            $totalProductionCost = 0;
            $log = ProductionLog::create([
                'recipe_id' => $recipe->id,
                'user_id' => auth()->id(),
                'portions' => $portions,
                'cost_per_portion' => 0,
                'total_cost' => 0,
            ]);

            // 2. Deduct Stock & Create Inventory Logs
            // IMPORTANT: This section DEDUCTS raw ingredients (STOCK OUT)
            // Raw ingredients should DECREASE when cooking
            $yieldValue = $recipe->yield_portions ?? $recipe->yields ?? 1;

            \Log::info("Production Processing - DEDUCTING RAW INGREDIENTS", [
                'recipe_id' => $recipe->id,
                'recipe_name' => $recipe->name,
                'portions' => $portions,
                'yieldValue' => $yieldValue,
                'ingredients_count' => $recipe->ingredients->count(),
                'action' => 'STOCK_OUT',
            ]);

            foreach ($recipe->ingredients as $ingredient) {
                // CRITICAL: Raw ingredients must be DEDUCTED (decreased)
                // quantity_change must be NEGATIVE for raw ingredients
                // Calculate quantity per portion
                // Calculate required quantity with unit conversion
                $requiredQtyInRecipeUnit = ($ingredient->pivot->quantity / $yieldValue) * $portions;
                
                $recipeUnit = Unit::tryFrom($ingredient->pivot->unit);
                $stockUnit = Unit::tryFrom($ingredient->measurement_unit);

                if ($recipeUnit && $stockUnit && $recipeUnit->canConvertTo($stockUnit)) {
                    $deductAmount = $recipeUnit->convertTo($requiredQtyInRecipeUnit, $stockUnit);
                } else {
                    $deductAmount = $requiredQtyInRecipeUnit;
                }

                // Refresh ingredient to get latest stock from database
                $ingredient->refresh();
                $before = $ingredient->current_stock;

                // CRITICAL: FIFO Stock Deduction
                // This replaces the simple current_stock update
                try {
                    $batchCost = $this->fifoService->deductStock(
                        $ingredient, 
                        $deductAmount, 
                        'Production', 
                        $log->id
                    );
                    $totalProductionCost += $batchCost;
                } catch (\Exception $e) {
                    \Log::error("FIFO Deduction Failed", [
                        'ingredient_id' => $ingredient->id,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }

                // Update database current_stock (DECREASING) for display/search consistency
                $ingredient->refresh();
                $after = $ingredient->current_stock;

                // Verify stock actually decreased
                if ($after >= $before && $before > 0 && $deductAmount > 0) {
                    \Log::error("CRITICAL: Stock did not decrease!", [
                        'ingredient_id' => $ingredient->id,
                        'ingredient_name' => $ingredient->name,
                        'before' => $before,
                        'after' => $after,
                        'deductAmount' => $deductAmount,
                    ]);
                }

                // Log for debugging
                \Log::info("Raw Ingredient Stock Deduction", [
                    'ingredient_id' => $ingredient->id,
                    'ingredient_name' => $ingredient->name,
                    'before' => $before,
                    'deductAmount' => $deductAmount,
                    'after' => $after,
                    'recipe_id' => $recipe->id,
                    'portions' => $portions,
                    'verification' => $after < $before ? 'CORRECT (decreased)' : 'ERROR (increased!)',
                ]);

                // CRITICAL: Create inventory log with NEGATIVE quantity_change (STOCK OUT/DEDUCTION)
                // Raw ingredients MUST decrease when cooking
                // quantity_change MUST be negative for raw materials
                $quantityChange = -$deductAmount; // Negative value (STOCK OUT)

                $inventoryLog = InventoryLog::create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => auth()->id(),
                    'quantity_change' => $quantityChange, // NEGATIVE for deduction (STOCK OUT)
                    'action' => 'RECIPE_USE',
                    'production_log_id' => $log->id,
                    'recipe_id' => $recipe->id,
                    'stock_before' => $before,
                    'stock_after' => $after,
                ]);

                // Verify log was created correctly - MUST be negative
                if ($inventoryLog->quantity_change >= 0) {
                    \Log::error("CRITICAL ERROR: Raw ingredient quantity_change is NOT negative!", [
                        'log_id' => $inventoryLog->id,
                        'ingredient_id' => $ingredient->id,
                        'ingredient_name' => $ingredient->name,
                        'quantity_change' => $inventoryLog->quantity_change,
                        'expected' => 'negative value',
                    ]);
                }

                \Log::info("Raw Ingredient Stock DEDUCTED (STOCK OUT)", [
                    'log_id' => $inventoryLog->id,
                    'ingredient_id' => $ingredient->id,
                    'ingredient_name' => $ingredient->name,
                    'quantity_change' => $inventoryLog->quantity_change,
                    'action' => $inventoryLog->action,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'deductAmount' => $deductAmount,
                    'verification' => $inventoryLog->quantity_change < 0 ? 'CORRECT (negative)' : 'ERROR (positive!)',
                ]);
            }

            // 3. Finalize Production Log with Actual FIFO Cost
            $log->update([
                'cost_per_portion' => $portions > 0 ? ($totalProductionCost / $portions) : 0,
                'total_cost' => $totalProductionCost,
            ]);

            // 4. Add Output Stock (if this is a sub-recipe that produces an ingredient)
            // IMPORTANT: This section ADDS stock to PRODUCED ingredient (STOCK IN)
            // This is ONLY for finished items produced by sub-recipes
            // Raw ingredients are already deducted above
            if ($recipe->isSubRecipe()) {
                $producedIngredient = $recipe->producesIngredient;

                \Log::info("Sub-Recipe Production - ADDING PRODUCED INGREDIENT STOCK", [
                    'recipe_id' => $recipe->id,
                    'recipe_name' => $recipe->name,
                    'produced_ingredient_id' => $producedIngredient->id,
                    'produced_ingredient_name' => $producedIngredient->name,
                    'action' => 'STOCK_IN',
                ]);

                // Calculate output amount
                $outputPerYield = $recipe->output_quantity;
                $totalOutput = $outputPerYield * $portions;


                // No unit conversion needed
                $addAmount = $totalOutput;

                $before = $producedIngredient->current_stock;
                $after = $before + $addAmount;

                $producedIngredient->update(['current_stock' => $after]);

                // CRITICAL: Create inventory log with POSITIVE quantity_change (STOCK IN)
                // This is ONLY for the PRODUCED ingredient (finished item), NOT raw materials
                // Raw materials were already deducted above with negative quantity_change
                $producedLog = InventoryLog::create([
                    'ingredient_id' => $producedIngredient->id,
                    'user_id' => auth()->id(),
                    'quantity_change' => abs($addAmount), // POSITIVE for produced ingredient (STOCK IN)
                    'action' => 'RECIPE_PRODUCTION',
                    'production_log_id' => $log->id,
                    'recipe_id' => $recipe->id,
                    'stock_before' => $before,
                    'stock_after' => $after,
                ]);

                \Log::info("Produced Ingredient Stock ADDED (STOCK IN)", [
                    'log_id' => $producedLog->id,
                    'ingredient_id' => $producedIngredient->id,
                    'ingredient_name' => $producedIngredient->name,
                    'quantity_change' => $producedLog->quantity_change,
                    'action' => $producedLog->action,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'note' => 'This is the FINISHED ITEM, not raw materials',
                ]);
            }
        });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        // Build success message with inventory deduction details
        $deductedDetails = [];
        foreach ($recipe->ingredients as $ingredient) {
            $ingredient->refresh(); // Refresh to get updated current_stock
            $deductedDetails[] = $ingredient->name;
        }
        $message = "Production recorded! {$portions} portions of {$recipe->name} cooked. ";
        $message .= "Master inventory updated for " . count($deductedDetails) . " ingredient(s).";

        return redirect()->route('production.create')->with('success', $message);
    }

    /**
     * Download Excel template for production upload
     */
    public function downloadTemplate(string $kitchen_slug)
    {
        return Excel::download(new ProductionTemplateExport, 'production_template.xlsx');
    }

    /**
     * Process uploaded Excel file for bulk production
     */
    public function uploadExcel(Request $request, string $kitchen_slug)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $data = Excel::toArray(new \App\Imports\DataImport, $file);

            if (empty($data) || empty($data[0])) {
                return back()->with('error', 'Excel file is empty or invalid.');
            }

            $rows = $data[0];
            $header = array_shift($rows); // Remove header row

            // Validate header
            $expectedHeaders = ['Recipe Name', 'Quantity', 'Unit Type (batches/portions)'];
            if (
                count($header) < 3 ||
                strtolower(trim($header[0])) !== 'recipe name' ||
                strtolower(trim($header[1])) !== 'quantity' ||
                !str_contains(strtolower(implode(' ', $header)), 'unit type')
            ) {
                return back()->with('error', 'Invalid Excel format. Please download the sample template first.');
            }

            $successCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // +2 because header is row 1, and array is 0-indexed

                // Skip empty rows
                if (empty($row[0]) || empty($row[1])) {
                    continue;
                }

                $recipeName = trim($row[0]);
                $quantity = $row[1];
                $unitType = isset($row[2]) ? strtolower(trim($row[2])) : 'batches';

                // Validate unit type
                if (!in_array($unitType, ['batches', 'portions'])) {
                    $errors[] = "Row {$rowNum}: Invalid unit type '{$unitType}'. Must be 'batches' or 'portions'.";
                    continue;
                }

                // Find recipe by name
                $recipe = Recipe::where('name', $recipeName)
                    ->where('status', \App\Enums\RecipeStatus::Permanent)
                    ->first();

                if (!$recipe) {
                    $errors[] = "Row {$rowNum}: Recipe '{$recipeName}' not found or not approved.";
                    continue;
                }

                // Process production (same logic as store method)
                try {
                    $result = $this->processProduction($recipe, $quantity, $unitType);
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errors[] = "Row {$rowNum}: {$result['error']}";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNum}: " . $e->getMessage();
                }
            }

            if ($successCount > 0) {
                $message = "Successfully processed {$successCount} production(s).";
                if (!empty($errors)) {
                    $message .= " " . count($errors) . " error(s) occurred.";
                    session()->flash('upload_errors', $errors);
                }
                return back()->with('success', $message);
            } else {
                return back()->with('error', 'No productions were processed. ' . implode(' ', array_slice($errors, 0, 3)));
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Error processing Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Process a single production entry (extracted from store method for reuse)
     */
    private function processProduction($recipe, $quantity, $unitType)
    {
        try {
            // Load recipe with all ingredients from all stages
            $recipe = Recipe::with(['stages.ingredients.ingredient'])->findOrFail($recipe->id);

            // Collect all ingredients from all stages, grouping by ingredient_id and summing quantities
            $ingredientsMap = [];
            foreach ($recipe->stages as $stage) {
                foreach ($stage->ingredients as $recipeIngredient) {
                    $ingredient = $recipeIngredient->ingredient;
                    if ($ingredient) {
                        $ingredientId = $ingredient->id;

                        if (!isset($ingredientsMap[$ingredientId])) {
                            // First occurrence - create pivot object
                            $ingredient->pivot = (object) [
                                'quantity' => $recipeIngredient->quantity,
                                'unit' => $recipeIngredient->unit,
                                'cost' => $recipeIngredient->cost,
                                'ingredient_group' => $recipeIngredient->ingredient_group,
                                'recipe_stage_id' => $recipeIngredient->recipe_stage_id,
                            ];
                            $ingredientsMap[$ingredientId] = $ingredient;
                        } else {
                            // Duplicate ingredient - sum quantities
                            $ingredientsMap[$ingredientId]->pivot->quantity += $recipeIngredient->quantity;
                        }
                    }
                }
            }

            // Replace the ingredients relation with our collected ingredients
            $recipe->setRelation('ingredients', collect(array_values($ingredientsMap)));

            // Calculate total portions
            $yieldValue = $recipe->yield_portions ?? $recipe->yields ?? 1;
            $portions = $quantity;
            if ($unitType === 'batches') {
                $portions = $quantity * $yieldValue;
            }

            // Check Stock Availability First
            $missingStock = [];
            foreach ($recipe->ingredients as $ingredient) {
                $requiredQtyInRecipeUnit = ($ingredient->pivot->quantity * $portions) / $yieldValue;
                
                $recipeUnit = Unit::tryFrom($ingredient->pivot->unit);
                $stockUnit = Unit::tryFrom($ingredient->measurement_unit);

                if ($recipeUnit && $stockUnit && $recipeUnit->canConvertTo($stockUnit)) {
                    $requiredQty = $recipeUnit->convertTo($requiredQtyInRecipeUnit, $stockUnit);
                } else {
                    $requiredQty = $requiredQtyInRecipeUnit;
                }

                if ($ingredient->current_stock < $requiredQty) {
                    $missingStock[] = $ingredient->name . " (Need: " . number_format($requiredQty, 3) . " " . $ingredient->measurement_unit . ", Have: " . number_format($ingredient->current_stock, 3) . ")";
                }
            }

            if (!empty($missingStock)) {
                return [
                    'success' => false,
                    'error' => 'Insufficient Stock: ' . implode(', ', array_slice($missingStock, 0, 3))
                ];
            }

            DB::transaction(function () use ($recipe, $portions) {
                // 1. Create Production Log placeholder
                $totalProductionCost = 0;
                $log = ProductionLog::create([
                    'recipe_id' => $recipe->id,
                    'user_id' => auth()->id(),
                    'portions' => $portions,
                    'cost_per_portion' => 0,
                    'total_cost' => 0,
                ]);

                // 2. Deduct Stock & Create Inventory Logs
                $yieldValue = $recipe->yield_portions ?? $recipe->yields ?? 1;
                foreach ($recipe->ingredients as $ingredient) {
                    $requiredQtyInRecipeUnit = ($ingredient->pivot->quantity / $yieldValue) * $portions;
                    
                    $recipeUnit = Unit::tryFrom($ingredient->pivot->unit);
                    $stockUnit = Unit::tryFrom($ingredient->measurement_unit);

                    if ($recipeUnit && $stockUnit && $recipeUnit->canConvertTo($stockUnit)) {
                        $deductAmount = $recipeUnit->convertTo($requiredQtyInRecipeUnit, $stockUnit);
                    } else {
                        $deductAmount = $requiredQtyInRecipeUnit;
                    }

                    // Refresh ingredient to get latest stock from database
                    $ingredient->refresh();
                    $before = $ingredient->current_stock;

                    // CRITICAL: FIFO Stock Deduction
                    try {
                        $batchCost = $this->fifoService->deductStock(
                            $ingredient, 
                            $deductAmount, 
                            'Production', 
                            $log->id
                        );
                        $totalProductionCost += $batchCost;
                    } catch (\Exception $e) {
                        \Log::error("FIFO Deduction Failed (Excel)", [
                            'ingredient_id' => $ingredient->id,
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }

                    // Update database current_stock (DECREASING)
                    $ingredient->refresh();
                    $after = $ingredient->current_stock;

                    // Verify the update was correct - stock must decrease
                    $ingredient->refresh();
                    if (abs($ingredient->current_stock - $after) > 0.001) {
                        \Log::error("Stock update mismatch (Excel)", [
                            'expected' => $after,
                            'actual' => $ingredient->current_stock,
                            'ingredient_id' => $ingredient->id
                        ]);
                    }

                    // Verify stock actually decreased
                    if ($after >= $before && $before > 0) {
                        \Log::error("CRITICAL (Excel): Stock did not decrease!", [
                            'ingredient_id' => $ingredient->id,
                            'ingredient_name' => $ingredient->name,
                            'before' => $before,
                            'after' => $after,
                            'deductAmount' => $deductAmount,
                        ]);
                    }

                    // Log for debugging
                    \Log::info("Raw Ingredient Stock Deduction (Excel)", [
                        'ingredient_id' => $ingredient->id,
                        'ingredient_name' => $ingredient->name,
                        'before' => $before,
                        'deductAmount' => $deductAmount,
                        'after' => $after,
                        'recipe_id' => $recipe->id,
                        'portions' => $portions,
                        'verification' => $after < $before ? 'CORRECT (decreased)' : 'ERROR (increased!)',
                    ]);

                    // CRITICAL: Create inventory log with NEGATIVE quantity_change (STOCK OUT/DEDUCTION)
                    // Raw ingredients MUST decrease when cooking
                    // quantity_change MUST be negative for raw materials
                    $quantityChange = -$deductAmount; // Negative value (STOCK OUT)

                    $inventoryLog = InventoryLog::create([
                        'ingredient_id' => $ingredient->id,
                        'user_id' => auth()->id(),
                        'quantity_change' => $quantityChange, // NEGATIVE for deduction (STOCK OUT)
                        'action' => 'RECIPE_USE',
                        'production_log_id' => $log->id,
                        'recipe_id' => $recipe->id,
                        'stock_before' => $before,
                        'stock_after' => $after,
                    ]);

                    // Verify log was created correctly - MUST be negative
                    if ($inventoryLog->quantity_change >= 0) {
                        \Log::error("CRITICAL ERROR (Excel): Raw ingredient quantity_change is NOT negative!", [
                            'log_id' => $inventoryLog->id,
                            'ingredient_id' => $ingredient->id,
                            'ingredient_name' => $ingredient->name,
                            'quantity_change' => $inventoryLog->quantity_change,
                            'expected' => 'negative value',
                        ]);
                    }

                    \Log::info("Raw Ingredient Stock DEDUCTED (STOCK OUT) - Excel Upload", [
                        'log_id' => $inventoryLog->id,
                        'ingredient_id' => $ingredient->id,
                        'ingredient_name' => $ingredient->name,
                        'quantity_change' => $inventoryLog->quantity_change,
                        'action' => $inventoryLog->action,
                        'stock_before' => $before,
                        'stock_after' => $after,
                        'deductAmount' => $deductAmount,
                        'verification' => $inventoryLog->quantity_change < 0 ? 'CORRECT (negative)' : 'ERROR (positive!)',
                    ]);
                }

                // 3. Finalize Production Log with Actual FIFO Cost
                $log->update([
                    'cost_per_portion' => $portions > 0 ? ($totalProductionCost / $portions) : 0,
                    'total_cost' => $totalProductionCost,
                ]);

                // 4. Add Output Stock (if this is a sub-recipe that produces an ingredient)
                if ($recipe->isSubRecipe()) {
                    $producedIngredient = $recipe->producesIngredient;

                    $outputPerYield = $recipe->output_quantity;
                    $totalOutput = $outputPerYield * $portions;

                    // No unit conversion needed
                    $addAmount = $totalOutput;

                    $before = $producedIngredient->current_stock;
                    $after = $before + $addAmount;

                    $producedIngredient->update(['current_stock' => $after]);

                    // CRITICAL: Create inventory log with POSITIVE quantity_change (STOCK IN)
                    // This is ONLY for the PRODUCED ingredient (finished item), NOT raw materials
                    // Raw materials were already deducted above with negative quantity_change
                    $producedLog = InventoryLog::create([
                        'ingredient_id' => $producedIngredient->id,
                        'user_id' => auth()->id(),
                        'quantity_change' => abs($addAmount), // POSITIVE for produced ingredient (STOCK IN)
                        'action' => 'RECIPE_PRODUCTION',
                        'production_log_id' => $log->id,
                        'recipe_id' => $recipe->id,
                        'stock_before' => $before,
                        'stock_after' => $after,
                    ]);

                    \Log::info("Produced Ingredient Stock ADDED (STOCK IN) - Excel Upload", [
                        'log_id' => $producedLog->id,
                        'ingredient_id' => $producedIngredient->id,
                        'ingredient_name' => $producedIngredient->name,
                        'quantity_change' => $producedLog->quantity_change,
                        'action' => $producedLog->action,
                        'note' => 'This is the FINISHED ITEM, not raw materials',
                    ]);
                }
            });

            return ['success' => true];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
