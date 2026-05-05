<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseBatch extends Model
{
    protected $fillable = [
        'kitchen_id',
        'purchase_id',
        'ingredient_id',
        'quantity_initial',
        'quantity_remaining',
        'price_per_unit',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
