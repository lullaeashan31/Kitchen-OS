<?php

namespace App\Services;

use App\Models\Ingredient;
use Illuminate\Support\Str;

class IngredientService
{
    /**
     * Create or find an ingredient by name.
     * Name is auto-capitalized by the model mutator.
     */
    public function createOrFind(string $name, array $attributes = []): Ingredient
    {
        $name = Str::title(trim($name));

        return Ingredient::firstOrCreate(
            ['name' => $name],
            $attributes
        );
    }

    /**
     * Search ingredients for autocomplete.
     */
    public function search(string $query, int $limit = 10, bool $onlyApproved = true)
    {
        $builder = Ingredient::where('name', 'like', "%{$query}%");
        
        if ($onlyApproved) {
            $builder->approved();
        }

        return $builder->limit($limit)->get();
    }

    /**
     * Update ingredient details.
     */
    public function update(Ingredient $ingredient, array $data): Ingredient
    {
        $ingredient->update($data);
        return $ingredient;
    }
}
