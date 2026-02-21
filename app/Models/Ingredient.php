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
        return $this->purchases()->where('status', 'approved')->sum('quantity');
    }

    /**
     * Vendor-wise purchase summary: vendor name, price (last), total qty from that vendor.
     * Only approved purchases; uses already loaded purchases when possible.
     */
    public function getVendorPurchaseSummaryAttribute()
    {
        $purchases = $this->relationLoaded('purchases')
            ? $this->purchases
            : $this->purchases()->where('status', 'approved')->with('vendor')->get();

        $byVendor = [];
        foreach ($purchases as $p) {
            $vid = $p->vendor_id ?? 'legacy';
            $vname = $p->vendor ? $p->vendor->name : ($p->vendor ?? 'N/A');
            if (!isset($byVendor[$vid])) {
                $byVendor[$vid] = ['name' => $vname, 'price' => $p->unit_price, 'quantity' => 0];
            }
            $byVendor[$vid]['quantity'] += (float) $p->quantity;
            $byVendor[$vid]['price'] = $p->unit_price; // keep last price
        }
        return collect(array_values($byVendor));
    }

    public function getTotalUsedAttribute()
    {
        // Sum all RECIPE_USE logs (quantity_change is negative for deductions)
        // We need the absolute value to show total used
        $totalUsed = $this->logs()
            ->where('action', 'RECIPE_USE')
            ->sum('quantity_change');
        
        // Since quantity_change is negative, we need to convert to positive
        return abs($totalUsed);
    }

    /**
     * Calculate current stock dynamically from inventory logs
     * This ensures accuracy by summing all quantity changes
     * Formula: Sum of all quantity_change from inventory_logs
     * 
     * IMPORTANT: quantity_change is:
     * - Positive for additions (purchases, adjustments, production output)
     * - Negative for deductions (recipe use, sales)
     * 
     * Logic:
     * 1. If logs exist, use sum of all logs (purchases are logged as purchase_approved)
     * 2. If no logs exist, prioritize approved purchases over database value
     * 3. If logs exist but purchases aren't logged, start with purchases then add log changes
     */
    public function getCalculatedCurrentStockAttribute()
    {
        $logCount = $this->logs()->count();
        $logsSum = $this->logs()->sum('quantity_change');
        $approvedPurchases = $this->purchases()->where('status', 'approved')->sum('quantity');
        
        // Check if purchases are logged (purchase_approved action exists)
        $purchaseLogsExist = $this->logs()->where('action', 'purchase_approved')->exists();
        
        if ($logCount === 0) {
            // No logs at all - prioritize approved purchases over database value
            // If purchases exist, use them; otherwise use database value
            if ($approvedPurchases > 0) {
                return $approvedPurchases;
            }
            return $this->attributes['current_stock'] ?? 0;
        }
        
        if ($purchaseLogsExist) {
            // Purchases are logged, so logs sum includes everything
            return $logsSum;
        } else {
            // Purchases exist but aren't logged - start with purchases, add log changes
            return $approvedPurchases + $logsSum;
        }
    }

    /**
     * Get current stock for display - use calculated value for accuracy
     * This ensures the displayed stock is always correct based on all transactions
     */
    public function getCurrentStockDisplayAttribute()
    {
        // Use calculated stock for accuracy
        $calculated = $this->getCalculatedCurrentStockAttribute();
        
        // Ensure stock is not negative (but allow 0)
        return max(0, $calculated);
    }
}
