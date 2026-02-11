<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'recipe_stage_id',
        'ingredient_id',
        'quantity',
        'unit',
        'cost',
        'ingredient_group',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'cost' => 'decimal:2',
        ];
    }

    // Relationships
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
