<?php

namespace App\Models;

use App\Enums\ProductionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_day_id',
        'recipe_id',
        'portions',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductionStatus::class,
        ];
    }

    // Relationships
    public function productionDay()
    {
        return $this->belongsTo(ProductionDay::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    // Accessors
    public function getScaledIngredientsAttribute()
    {
        return $this->recipe->scaleIngredients($this->portions);
    }

    public function getTotalCostAttribute()
    {
        return $this->recipe->total_cost * $this->portions;
    }
}
