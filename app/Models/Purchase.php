<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor', // Keep for backward compatibility or remove? Requirement says "Vendor free text field remove".
        // But we should keep it until migration is fully verified. 
        // Actually, let's keep it for now and deprecate later.
        'vendor_id',
        'ingredient_id',
        'quantity',
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
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
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

    public function getGoodsUrlAttribute()
    {
        if (!$this->goods_photo_path) {
            return null;
        }

        $disk = config('filesystems.default');

        // Check if file actually exists before generating URL
        if (!\Illuminate\Support\Facades\Storage::disk($disk)->exists($this->goods_photo_path)) {
            return null;
        }

        if (config("filesystems.disks.{$disk}.driver") === 's3') {
            return \Illuminate\Support\Facades\Storage::disk($disk)->temporaryUrl(
                $this->goods_photo_path,
                now()->addMinutes(30)
            );
        }

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->goods_photo_path);
    }
}
