<?php

namespace App\Services;

use App\Imports\DataImport;
use App\Models\Recipe;
use App\Models\Category;
use App\Models\User;
use App\Enums\RecipeStatus;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExcelImportService
{
    protected $ingredientService;
    protected $recipeService;

    public function __construct(IngredientService $ingredientService, RecipeService $recipeService)
    {
        $this->ingredientService = $ingredientService;
        $this->recipeService = $recipeService;
    }

    public function importRecipes(UploadedFile $file, User $user): array
    {
        $data = Excel::toArray(new DataImport, $file);
        $rows = collect($data[0] ?? []); // Assume first sheet

        if ($rows->isEmpty()) {
            return ['success' => 0, 'errors' => [['message' => 'Empty file']]];
        }

        // Group rows by Recipe Name
        $grouped = $rows->groupBy(function ($row) {
            return strtolower(trim($row['recipe_name'] ?? ''));
        });

        $processed = 0;
        $errors = [];

        foreach ($grouped as $recipeName => $recipeRows) {
            if (empty($recipeName))
                continue;

            try {
                DB::beginTransaction();

                // Take first row for common recipe data
                $firstRow = $recipeRows->first();

                // Validate Recipe Level
                $validator = Validator::make($firstRow, [
                    'recipe_name' => 'required|string',
                    'category' => 'required|string',
                    'method' => 'required|string',
                    'yields' => 'nullable|integer|min:1',
                ]);

                if ($validator->fails()) {
                    throw new \Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
                }

                // Process Category
                $category = Category::firstOrCreate(['name' => trim($firstRow['category'])]);

                // Check for existing recipe (Duplicate Detection MD5)
                $nameHash = md5(strtolower(trim($firstRow['recipe_name'])));
                // We don't store the hash, just compare Names case-insensitive
                $recipe = Recipe::whereRaw('LOWER(name) = ?', [strtolower(trim($firstRow['recipe_name']))])->first();

                $recipeData = [
                    'name' => trim($firstRow['recipe_name']),
                    'category_id' => $category->id,
                    'method' => $firstRow['method'],
                    'yields' => $firstRow['yields'] ?? 1,
                    // If creating new, default to Draft. If updating, keep status? Or reset to Draft?
                    // "Update existing recipes via reupload" -> usually reset to draft or keep?
                    // Let's reset to draft for safety on update too? Or keep.
                    // Let's assume creating new uses Draft.
                    'status' => RecipeStatus::Draft, // Enums used in Create
                ];

                $ingredientsData = [];

                // Process Ingredients from all rows for this recipe
                foreach ($recipeRows as $row) {
                    if (empty($row['ingredient_name']))
                        continue;

                    // Validate Ingredient Row
                    $ingValidator = Validator::make($row, [
                        'ingredient_name' => 'required|string',
                        'quantity' => 'required|numeric|min:0',
                        'unit' => 'required|string',
                        'cost' => 'nullable|numeric|min:0',
                    ]);

                    if ($ingValidator->fails()) {
                        throw new \Exception("Ingredient validation failed for '{$row['ingredient_name']}': " . implode(', ', $ingValidator->errors()->all()));
                    }

                    $ingredient = $this->ingredientService->createOrFind($row['ingredient_name']);

                    $ingredientsData[] = [
                        'ingredient_id' => $ingredient->id,
                        'quantity' => $row['quantity'],
                        'unit' => $row['unit'],
                        'cost' => $row['cost'] ?? 0,
                    ];
                }

                if (empty($ingredientsData)) {
                    throw new \Exception("No valid ingredients found for recipe");
                }

                $recipeData['ingredients'] = $ingredientsData;

                if ($recipe) {
                    // Update
                    $this->recipeService->updateRecipe($recipe, $recipeData, $user);
                } else {
                    // Create
                    $this->recipeService->createRecipe($recipeData, $user);
                }

                DB::commit();
                $processed++;

            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = [
                    'recipe' => $recipeName,
                    'error' => $e->getMessage(),
                    'row_data' => $recipeRows->first(), // Partial data for export
                ];
            }
        }

        return [
            'success' => $processed,
            'errors' => $errors,
        ];
    }

    public function importInventory(UploadedFile $file, User $user): array
    {
        $data = Excel::toArray(new DataImport, $file);
        $rows = collect($data[0] ?? []);

        if ($rows->isEmpty()) {
            return ['success' => 0, 'errors' => [['message' => 'Empty file']]];
        }

        $processed = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            // Skip empty rows
            if (empty($row['item_name']))
                continue;

            try {
                DB::beginTransaction();

                $validator = Validator::make($row, [
                    'inventory_id' => 'nullable|integer|exists:ingredients,id',
                    'item_name' => 'required|string',
                    'category' => 'nullable|string',
                    'measurement_unit' => 'required|string',
                    'purchase_unit' => 'nullable|string',
                    'price_per_unit' => 'required|numeric|min:0',
                    'vendor' => 'nullable|string',
                    'minimum_stock_level' => 'nullable|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    throw new \Exception(implode(', ', $validator->errors()->all()));
                }

                $attributes = [
                    'name' => trim($row['item_name']),
                    'measurement_unit' => trim($row['measurement_unit']),
                    'purchase_unit' => trim($row['purchase_unit'] ?? $row['measurement_unit']),
                    'price' => $row['price_per_unit'],
                    'vendor' => $row['vendor'] ?? null,
                    'alert_threshold' => $row['minimum_stock_level'] ?? 0,
                    // 'current_stock' - User didn't explicitly say upload sets stock, but usually it might.
                    // Prompt says "Upload master inventory... Purchase Entry Flow... current_stock += purchase...".
                    // Usually Master Upload sets initial stock too? "View stock levels".
                    // Use case: "Admin uploads Excel...". If I'm migrating, I want to set stock.
                    // BUT "Manual stock adjustment" exists.
                    // Let's allow setting stock if provided, else keep existing or 0?
                    // "Excel upload replaces existing items only if inventory_id matches."
                    // If simple update, maybe don't overwrite stock unless column exists?
                    // Let's assume if 'stock_quantity' is in Excel, we use it, else ignore.
                ];

                if (isset($row['current_stock'])) {
                    $attributes['current_stock'] = $row['current_stock'];
                }

                $categoryName = trim($row['category'] ?? '');
                if (!empty($categoryName)) {
                    $category = \App\Models\Category::firstOrCreate(
                        ['name' => $categoryName],
                        ['status' => 'active']
                    );
                    $attributes['category_id'] = $category->id;
                }

                if (!empty($row['inventory_id'])) {
                    // Update existing
                    $ingredient = \App\Models\Ingredient::find($row['inventory_id']);
                    $ingredient->update($attributes);
                } else {
                    // Create new
                    // Check duplicate name?
                    // "Each item gets unique inventory_id".
                    // If name exists, duplicate? User says "Excel upload replaces existing items only if inventory_id matches".
                    // Implies if I upload "Onion" without ID, and "Onion" exists, I might create duplicate "Onion" or fail?
                    // Safest: Update if name matches? Or strictly ID?
                    // "Excel upload replaces existing items only if inventory_id matches." -> This implies STRICT validation.
                    // If I upload "Onion" (no ID) and "Onion" exists -> Error or New ID?
                    // I will look for Name match as fallback to avoid chaos, or create new.
                    // Let's create new if no ID.
                    $ingredient = \App\Models\Ingredient::create($attributes);
                }

                DB::commit();
                $processed++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = [
                    'row' => $index + 2, // 1-header + 1-index
                    'item' => $row['item_name'] ?? 'N/A',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $processed,
            'errors' => $errors,
        ];
    }

    public function importSalesReport(UploadedFile $file, User $user): array
    {
        $data = Excel::toArray(new DataImport, $file);
        $rows = collect($data[0] ?? []);

        if ($rows->isEmpty()) {
            return ['success' => 0, 'errors' => [['message' => 'Empty file']]];
        }

        $processed = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if (empty($row['item_name']))
                continue;

            try {
                DB::beginTransaction();

                $validator = Validator::make($row, [
                    'item_name' => 'required|string',
                    'quantity_sold' => 'required|numeric|min:0.001',
                ]);

                if ($validator->fails()) {
                    throw new \Exception(implode(', ', $validator->errors()->all()));
                }

                $name = trim($row['item_name']);
                $qtySold = floatval($row['quantity_sold']);

                // Find ingredient by name (case-insensitive)
                $ingredient = \App\Models\Ingredient::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

                if (!$ingredient) {
                    throw new \Exception("Item '{$name}' not found in inventory.");
                }

                $oldStock = $ingredient->current_stock;
                $newStock = $oldStock - $qtySold;

                // We allow negative stock if necessary for sales (usually) but let's check system rules.
                // InventoryController@adjust says "Negative stock not allowed".
                // Let's stick to that or just log warning?
                // For sales report, maybe we should allow it but warn? 
                // Or strictly prevent. I'll strictly prevent to match InventoryController@adjust.
                if ($newStock < 0) {
                    throw new \Exception("Insufficient stock for '{$name}'. adjustment would result in negative stock.");
                }

                $ingredient->current_stock = $newStock;
                $ingredient->save();

                // Log the deduction
                \App\Models\InventoryLog::create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => $user->id,
                    'quantity_change' => -$qtySold,
                    'action' => 'sales_report',
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'reason' => 'Sales Report Upload',
                ]);

                DB::commit();
                $processed++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = [
                    'row' => $index + 2,
                    'item' => $row['item_name'] ?? 'N/A',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $processed,
            'errors' => $errors,
        ];
    }
}
