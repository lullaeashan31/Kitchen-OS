<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
        'ingredient_id',
        'user_id',
        'quantity_change',
        'action',
        'production_log_id',
        'recipe_id',
        'stock_before',
        'stock_after',
        'reason',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productionLog()
    {
        return $this->belongsTo(ProductionLog::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
