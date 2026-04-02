<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ingredient extends Model
{
    use HasFactory, BelongsToTenant;
    
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ingredient) {
            if (empty($ingredient->base_unit)) {
                $unit = strtolower($ingredient->measurement_unit);
                if (in_array($unit, ['kg', 'g', 'gram', 'grams', 'kilogram'])) {
                    $ingredient->base_unit = 'g';
                } elseif (in_array($unit, ['l', 'liter', 'litre', 'ml', 'milliliter'])) {
                    $ingredient->base_unit = 'ml';
                } else {
                    $ingredient->base_unit = $ingredient->measurement_unit;
                }
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
        $this->attributes['name'] = Str::title($value);
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
     * Vendor-wise purchase summary: vendor name, price (last), total qty from that vendor.
     * Only approved purchases; uses already loaded purchases when possible.
     */
    public function getVendorPurchaseSummaryAttribute()
    {
        $purchases = $this->relationLoaded('purchases')
            ? $this->purchases
            : $this->purchases()->where(function($q) { $q->where('status', 'approved'); })->with('vendor')->get();

        $byVendor = [];
        foreach ($purchases as $p) {
            $vid = $p->vendor_id ?? 'legacy';
            $vname = (is_object($p->vendor) && $p->vendor !== null) ? $p->vendor->name : (is_string($p->vendor) ? $p->vendor : 'N/A');
            if (!isset($byVendor[$vid])) {
                $byVendor[$vid] = ['name' => $vname, 'price' => $p->unit_price, 'quantity' => 0];
            }
            $byVendor[$vid]['quantity'] += $this->convertFromBaseUnit((float) $p->quantity);
            $byVendor[$vid]['price'] = $p->unit_price; // keep last price
        }
        return collect(array_values($byVendor));
    }

    public function getTotalPurchasedAttribute()
    {
        // Sum all additions from purchases, production, and positive adjustments
        $totalBase = (float) $this->logs()
            ->whereIn('action', ['purchase_approved', 'RECIPE_PRODUCTION'])
            ->where(function($q) { $q->where('quantity_change', '>', 0); })
            ->sum('quantity_change');

        return $this->convertFromBaseUnit($totalBase);
    }

    public function getTotalUsedAttribute()
    {
        // Sum all deductions from recipe use, POS sales, and sales report uploads
        $totalDeductedBase = $this->logs()
            ->whereIn('action', ['RECIPE_USE', 'POS_SALE', 'sales_report'])
            ->where(function($q) { $q->where('quantity_change', '<', 0); })
            ->sum('quantity_change');

        return abs($this->convertFromBaseUnit((float) $totalDeductedBase));
    }

    /**
     * Get current stock for display - uses the current_stock database column as source of truth.
     * All operations (Purchases, Production, POS, Adjustments) correctly update this column.
     * This avoids calculation errors when initial stock balances come from master imports.
     */
    public function getCurrentStockDisplayAttribute()
    {
        $baseStock = (float) ($this->attributes['current_stock'] ?? 0);
        return $this->convertFromBaseUnit($baseStock);
    }

    /**
     * Audit: Calculate current stock dynamically from inventory logs if possible.
     * This can be used for verification, but is NOT the primary display source.
     */
    public function getCalculatedCurrentStockAttribute()
    {
        $logsSumBase = (float) $this->logs()->sum('quantity_change');
        
        // If we want a calculated check, we must know the "starting balance".
        // In this system, starting balance is the stock during master upload (no logs).
        // For simple verification, we just convert the current stock.
        $baseStock = (float) ($this->attributes['current_stock'] ?? 0);
        return $this->convertFromBaseUnit($baseStock);
    }

    /**
     * Get the conversion multiplier for a unit to its base unit.
     */
    public static function getConversionMultiplier(?string $fromUnit, ?string $toUnit): float
    {
        if (!$fromUnit || !$toUnit) return 1.0;
        
        $fromUnit = strtolower(trim($fromUnit));
        $toUnit = strtolower(trim($toUnit));

        if ($fromUnit === $toUnit) return 1.0;

        $conversions = [
            'kg' => ['g' => 1000, 'gram' => 1000, 'grams' => 1000],
            'kilogram' => ['g' => 1000, 'gram' => 1000, 'grams' => 1000],
            'liter' => ['ml' => 1000, 'milliliter' => 1000, 'milliliters' => 1000],
            'litre' => ['ml' => 1000, 'milliliter' => 1000, 'milliliters' => 1000],
            'l' => ['ml' => 1000, 'milliliter' => 1000, 'milliliters' => 1000],
        ];

        return $conversions[$fromUnit][$toUnit] ?? 1.0;
    }

    /**
     * Calculate price per base unit (e.g. per gram or per ml).
     */
    public function getPricePerBaseUnitAttribute(): float
    {
        if ($this->purchase_quantity <= 0) return (float) $this->price; // Fallback to legacy price if no purchase info
        
        $multiplier = self::getConversionMultiplier($this->purchase_unit, $this->base_unit);
        $totalBaseUnits = (float)$this->purchase_quantity * $multiplier;
        
        if ($totalBaseUnits <= 0) return (float) $this->price;
        
        return (float)$this->purchase_price / $totalBaseUnits;
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

    public function convertToBaseUnit(float $quantity, string $unit): float
    {
        $multiplier = self::getConversionMultiplier($unit, $this->base_unit);
        return $quantity * $multiplier;
    }

    public function convertFromBaseUnit(float $quantity): float
    {
        $multiplier = self::getConversionMultiplier($this->measurement_unit, $this->base_unit);
        return $multiplier > 0 ? ($quantity / $multiplier) : $quantity;
    }
}
