<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\User;
use App\Enums\RecipeStatus;
use App\Enums\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\DriveService;
use App\Services\FIFOInventoryService;

class RecipeService
{
    protected $driveService;
    protected $googleDriveService;
    protected $fifoService;

    public function __construct(DriveService $driveService, GoogleDriveService $googleDriveService, FIFOInventoryService $fifoService)
    {
        $this->driveService = $driveService;
        $this->googleDriveService = $googleDriveService;
        $this->fifoService = $fifoService;
    }

    /**
     * Create a new recipe.
     */
    public function createRecipe(array $data, User $user): Recipe
    {
        return DB::transaction(function () use ($data, $user) {
            try {
                Log::info('Creating recipe', [
                    'name' => $data['name'] ?? 'N/A',
                    'category_id' => $data['category_id'] ?? null,
                    'stages_count' => count($data['stages'] ?? []),
                ]);

                $recipe = Recipe::create([
                    'name' => $data['name'],
                    'category_id' => $data['category_id'],
                    'method' => $data['method'] ?? '', // Legacy/Global method - default to empty string if null
                    // Map new yield fields to legacy 'yields' for compatibility/calculations
                    'yields' => $data['yield_portions'] ?? $data['yield_batches'] ?? 1,
                    'yield_portions' => $data['yield_portions'] ?? null,
                    'yield_weight' => $data['yield_weight'] ?? null,
                    'yield_weight_grams' => $data['yield_weight_grams'] ?? null,
                    'yield_weight_unit' => $data['yield_weight_unit'] ?? null,
                    'yield_volume' => $data['yield_volume'] ?? null,
                    'yield_volume_unit' => $data['yield_volume_unit'] ?? null,
                    'yield_batches' => $data['yield_batches'] ?? null,
                    'prep_time_minutes' => $data['prep_time_minutes'] ?? null,
                    'status' => RecipeStatus::Draft,
                    'created_by' => $user->id,
                    'version' => 1,
                    'is_sub_recipe' => $data['is_sub_recipe'] ?? false,
                    'produces_ingredient_id' => $data['produces_ingredient_id'] ?? null,
                    'output_quantity' => $data['output_quantity'] ?? null,
                    'output_unit' => $data['output_unit'] ?? null,
                ]);

                Log::info('Recipe created successfully', ['recipe_id' => $recipe->id]);
            } catch (\Exception $e) {
                Log::error('Failed to create recipe record', [
                    'error' => $e->getMessage(),
                    'data' => $data,
                ]);
                throw $e;
            }

            // Auto-create ingredient if sub-recipe mode is on and no ingredient linked
            if ($recipe->is_sub_recipe && !$recipe->produces_ingredient_id) {
                $ingredient = \App\Models\Ingredient::firstOrCreate(
                    ['name' => $recipe->name],
                    [
                        'status' => 'approved', // Auto-approved for sub-recipes
                        'price' => 0,
                        'measurement_unit' => $recipe->output_unit ?? 'pcs',
                        'category_id' => $recipe->category_id,
                    ]
                );
                $recipe->update(['produces_ingredient_id' => $ingredient->id]);
            }

            try {
                $this->syncStages($recipe, $data['stages'] ?? []);
                Log::info('Stages synced successfully', ['recipe_id' => $recipe->id]);
            } catch (\Exception $e) {
                Log::error('Failed to sync stages', [
                    'recipe_id' => $recipe->id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }

            // Calculate and save costs immediately
            try {
                $this->recalculateRecipeCosts($recipe);
                Log::info('Costs calculated during creation', ['recipe_id' => $recipe->id]);
            } catch (\Exception $e) {
                Log::warning('Failed to calculate costs during creation', [
                    'recipe_id' => $recipe->id,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                \App\Models\AuditLog::log('Created Recipe', $recipe, null, $recipe->toArray());
            } catch (\Exception $e) {
                Log::warning('Failed to create audit log', ['error' => $e->getMessage()]);
                // Don't fail recipe creation if audit log fails
            }

            // Auto-save PDF to Google Drive (optional, don't fail if it fails)
            try {
                $driveFileId = $this->googleDriveService->saveRecipePdfToDrive($recipe);
                if ($driveFileId) {
                    $recipe->update(['drive_file_id' => $driveFileId]);
                    Log::info('Recipe PDF saved to Google Drive', ['recipe_id' => $recipe->id]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to save recipe PDF to Google Drive', [
                    'recipe_id' => $recipe->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail recipe creation if Google Drive save fails
            }

            return $recipe;
        });
    }

    /**
     * Update an existing recipe.
     */
    public function updateRecipe(Recipe $recipe, array $data, User $user): Recipe
    {
        return DB::transaction(function () use ($recipe, $data, $user) {
            $oldData = $recipe->toArray();

            $recipe->update([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'method' => $data['method'] ?? $recipe->method,
                'yields' => $data['yield_portions'] ?? $data['yield_batches'] ?? $recipe->yields,
                'yield_portions' => $data['yield_portions'] ?? $recipe->yield_portions,
                'yield_weight' => $data['yield_weight'] ?? $recipe->yield_weight,
                'yield_weight_grams' => $data['yield_weight_grams'] ?? $recipe->yield_weight_grams,
                'yield_weight_unit' => $data['yield_weight_unit'] ?? $recipe->yield_weight_unit,
                'yield_volume' => $data['yield_volume'] ?? $recipe->yield_volume,
                'yield_volume_unit' => $data['yield_volume_unit'] ?? $recipe->yield_volume_unit,
                'yield_batches' => $data['yield_batches'] ?? $recipe->yield_batches,
                'prep_time_minutes' => $data['prep_time_minutes'] ?? $recipe->prep_time_minutes,
                'is_sub_recipe' => $data['is_sub_recipe'] ?? $recipe->is_sub_recipe,
                'produces_ingredient_id' => $data['produces_ingredient_id'] ?? $recipe->produces_ingredient_id,
                'output_quantity' => $data['output_quantity'] ?? $recipe->output_quantity,
                'output_unit' => $data['output_unit'] ?? $recipe->output_unit,
            ]);

            // Auto-create/update ingredient if sub-recipe mode is on
            if ($recipe->is_sub_recipe && !$recipe->produces_ingredient_id) {
                $ingredient = \App\Models\Ingredient::firstOrCreate(
                    ['name' => $recipe->name],
                    [
                        'status' => 'approved',
                        'price' => 0,
                        'measurement_unit' => $recipe->output_unit ?? 'pcs',
                        'category_id' => $recipe->category_id,
                    ]
                );
                $recipe->update(['produces_ingredient_id' => $ingredient->id]);
            }

            $this->syncStages($recipe, $data['stages'] ?? []);

            // Recalculate costs using fresh FIFO data
            $this->recalculateRecipeCosts($recipe);

            \App\Models\AuditLog::log('Updated Recipe', $recipe, $oldData, $recipe->toArray());

            // Auto-save PDF to Google Drive
            $driveFileId = $this->googleDriveService->saveRecipePdfToDrive($recipe);
            if ($driveFileId) {
                $recipe->update(['drive_file_id' => $driveFileId]);
            }

            return $recipe;
        });
    }

    /**
     * Sync Stages and their Ingredients
     */
    protected function syncStages(Recipe $recipe, array $stagesData): void
    {
        // 1. Get existing stage IDs associated with the recipe
        $existingStageIds = $recipe->stages()->pluck('id')->toArray();
        $processedStageIds = [];

        foreach ($stagesData as $index => $stageData) {
            // Find or Create Stage
            $stageId = $stageData['id'] ?? null;
            $stage = null;

            if ($stageId && in_array($stageId, $existingStageIds)) {
                $stage = \App\Models\RecipeStage::find($stageId);
                $stage->update([
                    'name' => $stageData['name'],
                    'method' => $stageData['method'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            } else {
                $stage = $recipe->stages()->create([
                    'name' => $stageData['name'],
                    'method' => $stageData['method'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }

            if ($stage) {
                $processedStageIds[] = $stage->id;
                // Sync Ingredients for this stage
                $this->syncIngredientsForStage($recipe, $stage, $stageData['ingredients'] ?? []);
            }
        }

        // 2. Delete removed stages
        $stagesToDelete = array_diff($existingStageIds, $processedStageIds);
        if (!empty($stagesToDelete)) {
            \App\Models\RecipeStage::whereIn('id', $stagesToDelete)->delete();
            // Cascading delete should handle ingredients based on DB constraint, 
            // but explicit cleanup in RecipeIngredient is also safe if constraints fail.
        }
    }

    /**
     * Sync ingredients to recipe stage.
     */
    protected function syncIngredientsForStage(Recipe $recipe, \App\Models\RecipeStage $stage, array $ingredients): void
    {
        // Detach existing ingredients for this stage
        // Use recipeIngredients() relation filtered by stage
        $stage->ingredients()->delete();

        // Skip if no ingredients provided (stages can exist without ingredients)
        if (empty($ingredients)) {
            Log::info('No ingredients provided for stage - this is allowed', [
                'recipe_id' => $recipe->id,
                'stage_id' => $stage->id,
            ]);
            return;
        }

        foreach ($ingredients as $item) {
            $ingredientId = $item['ingredient_id'] ?? null;
            $name = $item['name'] ?? null;

            // Skip if ingredient_id is missing or empty
            if (empty($ingredientId)) {
                Log::warning('Skipping ingredient with missing ingredient_id', [
                    'item' => $item,
                    'stage_id' => $stage->id,
                ]);
                continue;
            }

            // Handle Dynamic Creation
            if ($ingredientId && !is_numeric($ingredientId)) {
                $nameString = trim($ingredientId);
                $existing = \App\Models\Ingredient::where('name', $nameString)->first();

                if ($existing) {
                    $ingredientId = $existing->id;
                } else {
                    try {
                        $newDetails = [
                            'name' => $nameString,
                            'status' => 'pending',
                            'price' => 0,
                        ];
                        $newIng = \App\Models\Ingredient::create($newDetails);
                        $ingredientId = $newIng->id;
                        Log::info('Created new ingredient dynamically', [
                            'ingredient_id' => $ingredientId,
                            'name' => $nameString,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create ingredient dynamically', [
                            'name' => $nameString,
                            'error' => $e->getMessage(),
                        ]);
                        continue;
                    }
                }
            }

            // Fallback lookup
            if (!$ingredientId && $name) {
                $found = \App\Models\Ingredient::where('name', $name)->first();
                if ($found) {
                    $ingredientId = $found->id;
                }
            }

            if (!$ingredientId) {
                Log::warning('Could not resolve ingredient ID', [
                    'item' => $item,
                    'stage_id' => $stage->id,
                ]);
                continue;
            }

            $ingredient = \App\Models\Ingredient::find($ingredientId);

            if (!$ingredient) {
                Log::warning('Ingredient not found', [
                    'ingredient_id' => $ingredientId,
                    'stage_id' => $stage->id,
                ]);
                continue;
            }

            try {
                $baseUnit = $this->fifoService->resolveUnit($ingredient->measurement_unit);
                $recipeUnit = $this->fifoService->resolveUnit($item['unit']);
                $quantityToCost = (float)$item['quantity'];

                if ($recipeUnit && $baseUnit && $recipeUnit->canConvertTo($baseUnit)) {
                    // Convert recipe quantity to ingredient's base unit for accurate costing
                    $quantityInBaseUnit = $recipeUnit->convertTo($quantityToCost, $baseUnit);
                    // Use FIFO for costing estimate
                    $cost = $this->fifoService->calculateFIFOCost($ingredient, $quantityInBaseUnit);
                } else {
                    // Fallback to simplified costing if conversion not possible
                    $cost = $this->fifoService->calculateFIFOCost($ingredient, $quantityToCost);
                }

                \App\Models\RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'recipe_stage_id' => $stage->id,
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'cost' => $cost,
                    'ingredient_group' => $item['ingredient_group'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create recipe ingredient', [
                    'recipe_id' => $recipe->id,
                    'stage_id' => $stage->id,
                    'ingredient_id' => $ingredient->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with next ingredient instead of failing entire recipe
            }
        }
    }

    /**
     * Approve a recipe so it becomes permanent.
     */
    public function approve(Recipe $recipe, User $user): bool
    {
        if (!$user->canApproveRecipes()) {
            return false;
        }

        if ($recipe->isPermanent()) {
            return true;
        }

        try {
            // 1. Recalculate Costs using Latest Avg Cost
            $recipe->load('recipeIngredients.ingredient');

            foreach ($recipe->recipeIngredients as $recipeIngredient) {
                $ingredient = $recipeIngredient->ingredient;
                if (!$ingredient) {
                    continue;
                }

                $baseUnit = $this->fifoService->resolveUnit($ingredient->measurement_unit);
                $recipeUnit = $this->fifoService->resolveUnit($recipeIngredient->unit);
                $quantityToCost = (float)$recipeIngredient->quantity;

                try {
                    if ($recipeUnit && $baseUnit && $recipeUnit->canConvertTo($baseUnit)) {
                        $quantityInBaseUnit = $recipeUnit->convertTo($quantityToCost, $baseUnit);
                        $cost = $this->fifoService->calculateFIFOCost($ingredient, $quantityInBaseUnit);
                    } else {
                        $cost = $this->fifoService->calculateFIFOCost($ingredient, $quantityToCost);
                    }
                } catch (\Exception $e) {
                    $fallback = (float)($ingredient->avg_cost ?? $ingredient->price ?? 0);
                    $cost = $fallback * $quantityToCost;
                    Log::warning("approve: FIFO cost fallback for '{$ingredient->name}': " . $e->getMessage());
                }

                // Update cost
                $recipeIngredient->update(['cost' => $cost]);
            }

            // 2. Final Summation
            $recipe->refresh();
            $totalCost = $recipe->recipeIngredients()->sum('cost');
            $costPerPortion = $recipe->yields > 0 ? ($totalCost / $recipe->yields) : 0;

            DB::transaction(function () use ($recipe, $user, $totalCost, $costPerPortion) {
                $recipe->update([
                    'status' => RecipeStatus::Permanent,
                    'approved_by' => $user->id,
                    'total_cost' => $totalCost,
                    'cost_per_portion' => $costPerPortion,
                ]);

                // Try to generate HTML Recipe Card (optional, don't fail if view doesn't exist)
                try {
                    if (view()->exists('recipes.export.card')) {
                        $recipe->load(['category', 'stages.ingredients.ingredient', 'recipeIngredients.ingredient']);
                        $htmlContent = view('recipes.export.card', compact('recipe'))->render();
                        $filename = 'recipe_' . $recipe->id . '_' . \Illuminate\Support\Str::slug($recipe->name) . '.html';

                        // Upload to "Drive" (S3)
                        if ($this->driveService) {
                            $path = $this->driveService->uploadContent($htmlContent, $filename, 'recipes/cards');

                            // Link Drive File
                            \App\Models\DriveFile::create([
                                'name' => $recipe->name . ' - Card',
                                'drive_url' => null,
                                'path' => $path,
                                'file_id' => 's3_' . uniqid(),
                                'linked_type' => Recipe::class,
                                'linked_id' => $recipe->id,
                                'uploaded_by' => $user->id,
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail approval
                    \Log::warning('Failed to generate recipe card during approval: ' . $e->getMessage());
                }

                // Log approval
                \App\Models\AuditLog::log('Approved Recipe', $recipe);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Recipe approval failed: ' . $e->getMessage(), [
                'recipe_id' => $recipe->id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e; // Re-throw to show proper error
        }
    }

    /**
     * Scale a recipe to a new yield.
     * Creates a new version of the recipe with adjusted ingredient quantities.
     */
    public function scaleRecipe(Recipe $recipe, float $newYield, string $yieldType): Recipe
    {
        return DB::transaction(function () use ($recipe, $newYield, $yieldType) {
            // 1. Determine Scale Factor based on yield type
            $originalYield = 1;
            $scaleFactor = 1;

            // For now, we primarily support scaling by portions.
            if ($yieldType === 'portions') {
                $originalYield = $recipe->yield_portions ?? 1;
                if ($originalYield <= 0)
                    $originalYield = 1;
                $scaleFactor = $newYield / $originalYield;
            } elseif ($yieldType === 'batches') {
                $originalYield = $recipe->yield_batches ?? 1;
                if ($originalYield <= 0)
                    $originalYield = 1;
                $scaleFactor = $newYield / $originalYield;
            } else {
                $originalYield = 1;
                $scaleFactor = $newYield;
            }

            // 2. Duplicate Recipe
            $newRecipe = $recipe->replicate();
            // Append (Scaled) only if not already there, or maybe just increment version?
            // Requirement says "Scaling must create a NEW recipe version."
            // Usually versions share the same name/ID but have different version numbers?
            // But here replicate creates a NEW ID.
            // Let's just append " (Scaled)" for clarity as per plan.
            $newRecipe->name = $recipe->name . ' (Scaled x' . number_format($scaleFactor, 2) . ')';
            $newRecipe->status = RecipeStatus::Draft;
            $newRecipe->version = $recipe->version + 1;
            // $newRecipe->parent_id = $recipe->id; // If supported

            // Update Yields
            if ($yieldType === 'portions') {
                $newRecipe->yield_portions = $newYield;
            } elseif ($yieldType === 'batches') {
                $newRecipe->yield_batches = $newYield;
            }

            $newRecipe->yields = $newYield;
            $newRecipe->save();

            // 3. Duplicate Stages & Scale Ingredients
            foreach ($recipe->stages as $stage) {
                $newStage = $stage->replicate();
                $newStage->recipe_id = $newRecipe->id;
                $newStage->save();

                foreach ($stage->ingredients as $recipeIngredient) {
                    $newIngredientPivot = $recipeIngredient->replicate();
                    $newIngredientPivot->recipe_id = $newRecipe->id;
                    $newIngredientPivot->recipe_stage_id = $newStage->id;

                    // Scale Quantity
                    $newQuantity = $recipeIngredient->quantity * $scaleFactor;
                    $newIngredientPivot->quantity = $newQuantity;

                    // Scale Cost (Linear)
                    $newIngredientPivot->cost = $recipeIngredient->cost * $scaleFactor;
                    $newIngredientPivot->save();
                }
            }

            // 4. Update Totals
            $totalCost = $newRecipe->recipeIngredients()->sum('cost');
            $costPerPortion = $newRecipe->yields > 0 ? ($totalCost / $newRecipe->yields) : 0;

            $newRecipe->update([
                'total_cost' => $totalCost,
                'cost_per_portion' => $costPerPortion
            ]);

            \App\Models\AuditLog::log('Scaled Recipe', $newRecipe, ['factor' => $scaleFactor, 'original_id' => $recipe->id]);

            return $newRecipe;
        });
    }

    /**
     * Recalculate and update the costs for a recipe using current FIFO data.
     */
    public function recalculateRecipeCosts(Recipe $recipe): void
    {
        // Ensure stages and ingredients are fresh
        $recipe->load(['stages.ingredients.ingredient', 'recipeIngredients']);
        
        foreach ($recipe->stages as $stage) {
            foreach ($stage->ingredients as $recipeIngredient) {
                $ingredient = $recipeIngredient->ingredient;
                if (!$ingredient) continue;

                $baseUnit = $this->fifoService->resolveUnit($ingredient->measurement_unit);
                $recipeUnit = $this->fifoService->resolveUnit($recipeIngredient->unit);
                $quantityToCost = (float)$recipeIngredient->quantity;

                try {
                    if ($recipeUnit && $baseUnit && $recipeUnit->canConvertTo($baseUnit)) {
                        $quantityInBaseUnit = $recipeUnit->convertTo($quantityToCost, $baseUnit);
                        $cost = $this->fifoService->calculateFIFOCost($ingredient, $quantityInBaseUnit);
                    } else {
                        $cost = $this->fifoService->calculateFIFOCost($ingredient, $quantityToCost);
                    }
                } catch (\Exception $e) {
                    // Fallback to avg_cost * quantity if FIFO calculation fails
                    $fallback = (float)($ingredient->avg_cost ?? $ingredient->price ?? 0);
                    $cost = $fallback * $quantityToCost;
                    Log::warning("recalculateRecipeCosts fallback for '{$ingredient->name}': " . $e->getMessage());
                }

                $recipeIngredient->update(['cost' => $cost]);
            }
        }

        // Refresh to get updated ingredient costs before summing
        $totalCost = $recipe->recipeIngredients()->sum('cost');
        $costPerPortion = $recipe->yields > 0 ? ($totalCost / $recipe->yields) : 0;

        $recipe->update([
            'total_cost' => $totalCost,
            'cost_per_portion' => $costPerPortion
        ]);
    }
}
