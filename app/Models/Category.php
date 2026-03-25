<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
        'name',
        'status',
        'type',
    ];

    // Scopes
    public function scopeForIngredients($query)
    {
        return $query->where('type', 'ingredient');
    }

    public function scopeForRecipes($query)
    {
        return $query->where('type', 'recipe');
    }

    // Relationships
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }
}
