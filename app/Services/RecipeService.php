<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\User;
use App\Enums\RecipeStatus;
use Illuminate\Support\Facades\DB;
use App\Services\DriveService;

class RecipeService
{
    protected $unitService;
    protected $driveService;

    public function __construct(UnitConversionService $unitService, DriveService $driveService)
    {
        $this->unitService = $unitService;
        $this->driveService = $driveService;
    }

    /**
     * Create a new recipe.
     */
    public function createRecipe(array $data, User $user): Recipe
    {
        return DB::transaction(function () use ($data, $user) {
            $recipe = Recipe::create([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'method' => $data['method'],
                'yields' => $data['yields'] ?? 1,
                'yield_portions' => $data['yield_portions'] ?? null,
                'yield_weight' => $data['yield_weight'] ?? null,
                'yield_weight_unit' => $data['yield_weight_unit'] ?? null,
                'prep_time_minutes' => $data['prep_time_minutes'] ?? null,
                'status' => RecipeStatus::Draft,
                'created_by' => $user->id,
                'version' => 1,
            ]);

            $this->syncIngredients($recipe, $data['ingredients'] ?? []);

            // Calculate and save costs immediately
            $totalCost = $recipe->recipeIngredients()->sum('cost');
            $costPerPortion = $recipe->yields > 0 ? ($totalCost / $recipe->yields) : 0;

            $recipe->update([
                'total_cost' => $totalCost,
                'cost_per_portion' => $costPerPortion
            ]);

            \App\Models\AuditLog::log('Created Recipe', $recipe, null, $recipe->toArray());

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
                'method' => $data['method'],
                'yields' => $data['yields'] ?? $recipe->yields,
                'yield_portions' => $data['yield_portions'] ?? $recipe->yield_portions,
                'yield_weight' => $data['yield_weight'] ?? $recipe->yield_weight,
                'yield_weight_unit' => $data['yield_weight_unit'] ?? $recipe->yield_weight_unit,
                'prep_time_minutes' => $data['prep_time_minutes'] ?? $recipe->prep_time_minutes,
            ]);

            $this->syncIngredients($recipe, $data['ingredients'] ?? []);

            // Recalculate costs
            $recipe->refresh();
            $totalCost = $recipe->recipeIngredients()->sum('cost');
            $costPerPortion = $recipe->yields > 0 ? ($totalCost / $recipe->yields) : 0;

            $recipe->update([
                'total_cost' => $totalCost,
                'cost_per_portion' => $costPerPortion
            ]);

            \App\Models\AuditLog::log('Updated Recipe', $recipe, $oldData, $recipe->toArray());

            return $recipe;
        });
    }

    /**
     * Sync ingredients to recipe.
     * Enforces usage of Master Ingredient Price.
     */
    protected function syncIngredients(Recipe $recipe, array $ingredients): void
    {
        // Detach all existing ingredients first
        $recipe->ingredients()->detach();

        foreach ($ingredients as $item) {
            $ingredientId = $item['ingredient_id'] ?? null;
            $name = $item['name'] ?? null;

            // Handle Dynamic Creation (TomSelect create:true string value)
            if ($ingredientId && !is_numeric($ingredientId)) {
                $nameString = trim($ingredientId);
                // Try to find by name first
                $existing = \App\Models\Ingredient::where('name', $nameString)->first();

                if ($existing) {
                    $ingredientId = $existing->id;
                } else {
                    // Create Pending Ingredient
                    $newDetails = [
                        'name' => $nameString,
                        'status' => 'pending',
                        'price' => 0,
                    ];
                    $newIng = \App\Models\Ingredient::create($newDetails);
                    $ingredientId = $newIng->id;
                }
            }

            // Fallback lookup if ID is null but name provided
            if (!$ingredientId && $name) {
                $found = \App\Models\Ingredient::where('name', $name)->first();
                if ($found) {
                    $ingredientId = $found->id;
                }
            }

            if (!$ingredientId) {
                continue;
            }

            $ingredient = \App\Models\Ingredient::find($ingredientId);

            if ($ingredient) {
                $cost = 0;
                $unitCost = $ingredient->avg_cost > 0 ? $ingredient->avg_cost : $ingredient->price;

                if ($unitCost > 0) {
                    try {
                        if ($ingredient->measurement_unit) {
                            $quantityInBase = $this->unitService->convert(
                                $item['quantity'],
                                $item['unit'],
                                $ingredient->measurement_unit
                            );
                            $cost = $quantityInBase * $unitCost;
                        }
                    } catch (\Exception $e) {
                        $cost = 0;
                    }
                }

                // Attach allows duplicates if same ingredient used multiple times (e.g. diff groups)
                $recipe->ingredients()->attach($ingredient->id, [
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'cost' => $cost,
                    'ingredient_group' => $item['ingredient_group'] ?? null,
                ]);
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

        // 1. Recalculate Pivot Costs using Latest Avg Cost
        $recipe->load('ingredients');
        foreach ($recipe->ingredients as $ingredient) {
            $unitCost = $ingredient->avg_cost > 0 ? $ingredient->avg_cost : $ingredient->price;
            $cost = 0;

            if ($unitCost > 0) {
                try {
                    $quantityInBase = $this->unitService->convert(
                        $ingredient->pivot->quantity,
                        $ingredient->pivot->unit,
                        $ingredient->measurement_unit
                    );
                    $cost = $quantityInBase * $unitCost;
                } catch (\Exception $e) {
                    $cost = 0;
                }
            }

            // Update pivot without detaching
            $recipe->ingredients()->updateExistingPivot($ingredient->id, ['cost' => $cost]);
        }

        // 2. Final Summation
        $recipe->refresh(); // Reload relation with new pivot values
        $totalCost = $recipe->recipeIngredients()->sum('cost');
        $costPerPortion = $recipe->yields > 0 ? ($totalCost / $recipe->yields) : 0;

        DB::transaction(function () use ($recipe, $user, $totalCost, $costPerPortion) {
            $recipe->update([
                'status' => RecipeStatus::Permanent,
                'approved_by' => $user->id,
                'total_cost' => $totalCost,
                'cost_per_portion' => $costPerPortion,
            ]);

            // Generate HTML Recipe Card
            $htmlContent = view('recipes.export.card', compact('recipe'))->render();
            $filename = 'recipe_' . $recipe->id . '_' . \Illuminate\Support\Str::slug($recipe->name) . '.html';

            // Upload to "Drive"
            $publicUrl = $this->driveService->uploadContent($htmlContent, $filename, 'recipes/cards');

            // Link Drive File
            \App\Models\DriveFile::create([
                'name' => $recipe->name . ' - Card',
                'drive_url' => $publicUrl,
                'file_id' => 'mock_id_' . uniqid(), // Simulation
                'linked_type' => Recipe::class,
                'linked_id' => $recipe->id,
                'uploaded_by' => $user->id,
            ]);

            // Log approval
            \App\Models\AuditLog::log('Approved Recipe', $recipe);
        });

        return true;
    }
}
