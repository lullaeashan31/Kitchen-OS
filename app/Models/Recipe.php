<?php

namespace App\Models;

use App\Enums\RecipeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'method',
        'yields',
        'status',
        'version',
        'created_by',
        'approved_by',
        'total_cost',
        'cost_per_portion',

        'prep_time_minutes',
        'produces_ingredient_id',
        'output_quantity',
        'output_unit',
        'yield_portions',
        'yield_weight',
        'yield_weight_unit',
        'yield_volume',
        'yield_volume_unit',
        'yield_batches',
        'is_sub_recipe',
        'drive_file_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => RecipeStatus::class,
            'total_cost' => 'decimal:2',
            'cost_per_portion' => 'decimal:2',
            'is_sub_recipe' => 'boolean',
        ];
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->withPivot('quantity', 'unit', 'cost', 'ingredient_group', 'recipe_stage_id')
            ->withTimestamps();
    }

    public function stages()
    {
        return $this->hasMany(RecipeStage::class)->orderBy('sort_order');
    }

    public function recipeIngredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function versions()
    {
        return $this->hasMany(RecipeVersion::class)->orderBy('version', 'desc');
    }

    public function productionItems()
    {
        return $this->hasMany(ProductionItem::class);
    }

    public function driveFiles()
    {
        return $this->morphMany(DriveFile::class, 'linked');
    }

    public function producesIngredient()
    {
        return $this->belongsTo(Ingredient::class, 'produces_ingredient_id');
    }

    // Scopes
    public function scopePermanent($query)
    {
        return $query->where('status', RecipeStatus::Permanent);
    }

    public function scopeDrafts($query)
    {
        return $query->where('status', RecipeStatus::Draft);
    }

    // Helper Methods for Calculation (Separate from Attributes)
    public function calculateTotalCost(): float
    {
        return (float) $this->recipeIngredients->sum('cost');
    }

    public function getAllergensAttribute()
    {
        return $this->ingredients->flatMap(function ($ingredient) {
            return $ingredient->allergen_tags ?? [];
        })->unique()->values()->sort()->all();
    }

    // Helper Methods
    public function isDraft(): bool
    {
        return $this->status === RecipeStatus::Draft;
    }

    public function isPermanent(): bool
    {
        return $this->status === RecipeStatus::Permanent;
    }

    public function isSubRecipe(): bool
    {
        return (bool) $this->is_sub_recipe || !is_null($this->produces_ingredient_id);
    }

    public function canBeEditedBy(User $user): bool
    {
        // Locked: Permanent recipes cannot be edited.
        if ($this->isPermanent()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return true;
        }

        // Staff can only edit their own drafts
        if ($user->isStaff() && $this->isDraft() && $this->created_by === $user->id) {
            return true;
        }

        return false;
    }

    public function scaleIngredients(int $portions)
    {
        return $this->recipeIngredients->map(function ($recipeIngredient) use ($portions) {
            return [
                'ingredient' => $recipeIngredient->ingredient,
                'quantity' => $recipeIngredient->quantity * $portions,
                'unit' => $recipeIngredient->unit,
                'cost' => $recipeIngredient->cost * $portions,
            ];
        });
    }
}
