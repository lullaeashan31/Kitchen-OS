<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryDeduction extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
        'ingredient_id',
        'purchase_id',
        'quantity_used',
        'unit_price',
        'total_cost',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function source()
    {
        return $this->morphTo();
    }
}
