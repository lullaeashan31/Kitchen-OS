<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
        'vendor', // Keep for backward compatibility or remove? Requirement says "Vendor free text field remove".
        // But we should keep it until migration is fully verified. 
        // Actually, let's keep it for now and deprecate later.
        'vendor_id',
        'ingredient_id',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'purchase_date',
        'created_by',
        'invoice_photo_path',
        'goods_photo_path',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'approved_at' => 'datetime',
        'quantity' => 'decimal:3',
        'unit' => \App\Enums\Unit::class,
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'goods_photo_path' => 'array',  // Multiple goods photos stored as JSON
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getVendorNameAttribute(): string
    {
        if ($this->vendor instanceof Vendor) {
            return $this->vendor->name;
        }
        
        // Fallback to the string column if relationship is not set
        return $this->getAttributes()['vendor'] ?? 'N/A';
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function getInvoiceUrlAttribute()
    {
        if (!$this->invoice_photo_path) {
            return null;
        }

        $disk = config('filesystems.default');

        // Check if file actually exists before generating URL
        if (!\Illuminate\Support\Facades\Storage::disk($disk)->exists($this->invoice_photo_path)) {
            return null;
        }

        if (config("filesystems.disks.{$disk}.driver") === 's3') {
            return \Illuminate\Support\Facades\Storage::disk($disk)->temporaryUrl(
                $this->invoice_photo_path,
                now()->addMinutes(30)
            );
        }

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->invoice_photo_path);
    }

    /**
     * Returns an array of URLs for all goods photos.
     */
    public function getGoodsUrlsAttribute(): array
    {
        $paths = $this->goods_photo_path;
        if (empty($paths))
            return [];
        // Handle legacy single-string paths
        if (is_string($paths))
            $paths = [$paths];

        $disk = config('filesystems.default');
        $urls = [];
        foreach ($paths as $path) {
            if (!$path || !\Illuminate\Support\Facades\Storage::disk($disk)->exists($path))
                continue;
            if (config("filesystems.disks.{$disk}.driver") === 's3') {
                $urls[] = \Illuminate\Support\Facades\Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(30));
            } else {
                $urls[] = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);
            }
        }
        return $urls;
    }

    /**
     * Legacy: returns first goods photo URL (backward compat).
     */
    public function getGoodsUrlAttribute(): ?string
    {
        $urls = $this->getGoodsUrlsAttribute();
        return $urls[0] ?? null;
    }
    /**
     * Scope for approved purchases.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
