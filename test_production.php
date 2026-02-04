<?php

use App\Models\User;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\ProductionLog;
use App\Models\InventoryLog;
use App\Enums\RecipeStatus;
use App\Services\UnitConversionService;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Setup Data
$user = User::first() ?? User::factory()->create(['role' => \App\Enums\UserRole::Admin]);
auth()->login($user);

$ingredient = Ingredient::create([
    'name' => 'Test Ingredient ' . uniqid(),
    'measurement_unit' => 'kg',
    'current_stock' => 10, // 10kg
    'price' => 5,
    'total_purchased' => 10,
]);

$recipe = Recipe::create([
    'name' => 'Test Recipe ' . uniqid(),
    'category_id' => \App\Models\Category::first()->id ?? \App\Models\Category::create(['name' => 'Test Cat', 'status' => 'active'])->id,
    'status' => RecipeStatus::Permanent,
    'created_by' => $user->id,
    'yields' => 1,
    'cost_per_portion' => 1,
    'total_cost' => 1,
    'method' => 'Test method',
]);

// Attach ingredient: 1kg per yield
$recipe->ingredients()->attach($ingredient->id, [
    'quantity' => 1000, // 1000g
    'unit' => 'g',
    'cost' => 5,
]);

echo "Initial Stock: " . $ingredient->current_stock . "\n";
echo "Initial Total Used: " . $ingredient->total_used . "\n";

// 2. Simulate Production Controller Logic
$portions = 2; // Cook 2 portions using 2kg (2000g)

// Mock Unit Service
$unitService = app(UnitConversionService::class);

DB::transaction(function () use ($recipe, $portions, $unitService, $user) {
    // Log
    $log = ProductionLog::create([
        'recipe_id' => $recipe->id,
        'user_id' => $user->id,
        'portions' => $portions,
        'cost_per_portion' => $recipe->cost_per_portion,
        'total_cost' => $recipe->cost_per_portion * $portions,
    ]);

    foreach ($recipe->ingredients as $ing) {
        $qtyPerPortion = $ing->pivot->quantity / ($recipe->yields ?: 1);
        $requiredQty = $qtyPerPortion * $portions; // 1000g * 2 = 2000g

        $deductAmount = $unitService->convert(
            $requiredQty,
            $ing->pivot->unit,
            $ing->measurement_unit
        ); // Should convert 2000g -> 2kg

        echo "Deducting: $deductAmount {$ing->measurement_unit}\n";

        $before = $ing->current_stock;
        $after = $before - $deductAmount;

        $ing->update(['current_stock' => $after]);

        InventoryLog::create([
            'ingredient_id' => $ing->id,
            'user_id' => $user->id,
            'quantity_change' => -$deductAmount,
            'action' => 'RECIPE_USE',
            'production_log_id' => $log->id,
            'recipe_id' => $recipe->id,
            'stock_before' => $before,
            'stock_after' => $after,
        ]);
    }
});

// 3. Verify
$ingredient->refresh();
echo "Final Stock: " . $ingredient->current_stock . "\n";
echo "Final Total Used: " . $ingredient->total_used . "\n";

if ($ingredient->total_used == 2) {
    echo "SUCCESS: Total Used updated correctly.\n";
} else {
    echo "FAILURE: Total Used mismatch.\n";
}
