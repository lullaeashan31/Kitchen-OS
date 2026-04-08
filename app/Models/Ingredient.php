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
        $latest = $this->purchases()
            ->approved()
            ->orderBy('purchase_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest instanceof Purchase) {
            return (float) $latest->unit_price;
        }

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
            : $this->purchases()->where('status', 'approved')->with('vendor')->get();

        $byVendor = [];
        foreach ($purchases as $p) {
            $vid = $p->vendor_id ?? 'legacy';
            $vname = (is_object($p->vendor) && $p->vendor !== null) ? $p->vendor->name : (is_string($p->vendor) ? $p->vendor : 'N/A');
            if (!isset($byVendor[$vid])) {
                $byVendor[$vid] = ['name' => $vname, 'price' => $p->unit_price, 'quantity' => 0];
            }
            $byVendor[$vid]['quantity'] += (float) $p->quantity; // No conversion
            $byVendor[$vid]['price'] = $p->unit_price; // keep last price
        }
        return collect(array_values($byVendor));
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

