<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Enums\Unit;

class Ingredient extends Model
{
    use HasFactory, BelongsToTenant;
    
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ingredient) {
            // No automated unit conversion - use what's provided
            if (empty($ingredient->base_unit)) {
                $ingredient->base_unit = $ingredient->measurement_unit;
            }
        });
    }

    protected $fillable = [
        'kitchen_id',
        'name',
        'price',
        'avg_cost',
        'measurement_unit',
        'purchase_unit',
        'purchase_quantity',
        'purchase_price',
        'base_unit',
        'category_id',
        'vendor',
        'status',
        'current_stock',
        'alert_threshold',
        'allergen_tags',
        'storage_location',
        'created_by',
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
        $this->attributes['name'] = \Illuminate\Support\Str::title($value);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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

    /**
     * Get the latest unit price from approved purchases.
     */
    public function getLatestPriceAttribute(): float
    {
        // 1. Try to get price from latest approved purchase
        $latest = $this->purchases()
            ->approved()
            ->orderBy('purchase_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest instanceof Purchase) {
            $purchaseUnit = $latest->unit;
            $baseUnit = Unit::tryFrom($this->measurement_unit);

            if ($purchaseUnit && $baseUnit && $purchaseUnit->canConvertTo($baseUnit)) {
                $divisor = $purchaseUnit->convertTo(1, $baseUnit);
                if ($divisor > 0) {
                    return (float) ($latest->unit_price / $divisor);
                }
            }
            return (float) $latest->unit_price;
        }

        // 2. If no purchase, check if this ingredient is produced by a recipe
        $producingRecipe = $this->producedByRecipes()
            ->where('status', \App\Enums\RecipeStatus::Permanent->value) // Only from approved recipes
            ->latest()
            ->first();

        if ($producingRecipe) {
            // For sub-recipes that produce an ingredient, calculate cost per output unit
            // Example: 1000g produced for ₹100 total -> ₹0.10 per gram
            if ($producingRecipe->output_quantity > 0) {
                return (float) ($producingRecipe->total_cost / $producingRecipe->output_quantity);
            }
            
            // Fallback for simple portion-based sub-recipes
            return (float) ($producingRecipe->cost_per_portion ?? 0);
        }

        // 3. Fallback to base price
        return (float) ($this->price ?? 0);
    }

    /**
     * Vendor-wise purchase summary: vendor name, price (last), total qty from that vendor.
     * Only approved purchases; uses already loaded purchases when possible.
     */
    public function getVendorPurchaseSummaryAttribute()
    {
        $purchases = $this->relationLoaded('purchases')
            ? $this->purchases
            : $this->purchases()->where('status', 'approved')->orderBy('purchase_date', 'desc')->with('vendor')->get();

        $byVendor = [];
        $baseUnitEnum = Unit::tryFrom($this->measurement_unit);

        foreach ($purchases as $p) {
            $vid = $p->vendor_id ?? 'legacy';
            $vname = (is_object($p->vendor) && $p->vendor !== null) ? $p->vendor->name : (is_string($p->vendor) ? $p->vendor : 'N/A');
            
            if (!isset($byVendor[$vid])) {
                $byVendor[$vid] = [
                    'name' => $vname,
                    'unit_price' => (float) $p->unit_price,
                    'purchase_unit' => $p->unit,
                    'normalized_total' => 0,
                ];
            }

            $pUnit = $p->unit;
            $qty = (float) $p->quantity;
            
            $normalized = $qty;
            if ($pUnit instanceof Unit && $baseUnitEnum instanceof Unit && $pUnit->canConvertTo($baseUnitEnum)) {
                $normalized = $pUnit->convertTo($qty, $baseUnitEnum);
            }
            
            $byVendor[$vid]['normalized_total'] += $normalized;
            
            // Keep latest price/unit
            if (!isset($byVendor[$vid]['last_date']) || $p->purchase_date >= $byVendor[$vid]['last_date']) {
                $byVendor[$vid]['unit_price'] = (float) $p->unit_price;
                $byVendor[$vid]['purchase_unit'] = $pUnit;
                $byVendor[$vid]['last_date'] = $p->purchase_date;
            }
        }

        return collect(array_values($byVendor))->map(function($v) use ($baseUnitEnum) {
            $normalizedTotal = $v['normalized_total'];
            $pUnit = $v['purchase_unit'];
            
            $displayPurchaseQty = $normalizedTotal;
            $displayPurchaseUnit = $this->measurement_unit;

            if ($pUnit instanceof Unit && $baseUnitEnum instanceof Unit && $baseUnitEnum->canConvertTo($pUnit)) {
                $displayPurchaseQty = $baseUnitEnum->convertTo($normalizedTotal, $pUnit);
                $displayPurchaseUnit = $pUnit->value;
            } else if ($pUnit) {
                $displayPurchaseUnit = is_string($pUnit) ? $pUnit : $pUnit->value;
            }

            return [
                'name' => $v['name'],
                'unit_price' => $v['unit_price'],
                'purchase_unit' => $displayPurchaseUnit,
                'purchase_quantity' => $displayPurchaseQty,
                'normalized_quantity' => $normalizedTotal,
                'base_unit' => $this->measurement_unit
            ];
        });
    }

    public function getTotalPurchasedAttribute()
    {
        // Sum all additions from purchases, production, and positive adjustments
        return (float) $this->logs()
            ->whereIn('action', ['purchase_approved', 'RECIPE_PRODUCTION'])
            ->where('quantity_change', '>', 0)
            ->sum('quantity_change');
    }

    public function getTotalUsedAttribute()
    {
        // Sum all deductions from recipe use, POS sales, and sales report uploads
        $totalDeducted = $this->logs()
            ->whereIn('action', ['RECIPE_USE', 'POS_SALE', 'sales_report'])
            ->where('quantity_change', '<', 0)
            ->sum('quantity_change');

        return abs((float) $totalDeducted);
    }

    /**
     * Display current stock exactly as stored in DB.
     */
    public function getCurrentStockDisplayAttribute()
    {
        return (float) ($this->attributes['current_stock'] ?? 0);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Helpers
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}

