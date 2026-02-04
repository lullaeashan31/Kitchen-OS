<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'avg_cost',
        'measurement_unit',
        'purchase_unit',
        'category_id',
        'vendor',
        'status',
        'current_stock',
        'alert_threshold',
        'allergen_tags',
        'storage_location',
    ];

    protected function casts(): array
    {
        return [
            'allergen_tags' => 'array',
        ];
    }

    // Mutator to auto-capitalize name
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::title($value);
    }

    // Relationships
    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
            ->withPivot('quantity', 'unit', 'cost')
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function driveFiles()
    {
        return $this->morphMany(DriveFile::class, 'linked');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function producedByRecipes()
    {
        return $this->hasMany(Recipe::class, 'produces_ingredient_id');
    }

    // Accessors
    public function getTotalPurchasedAttribute()
    {
        return $this->purchases()->sum('quantity');
    }

    public function getTotalUsedAttribute()
    {
        // usage is negative, so we sum it and invert
        return abs($this->logs()
            ->where('action', 'RECIPE_USE')
            ->sum('quantity_change'));
    }
}
