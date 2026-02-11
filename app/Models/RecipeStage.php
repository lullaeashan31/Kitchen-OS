<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'name',
        'method',
        'sort_order',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }
}
