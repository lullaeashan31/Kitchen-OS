<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PurchaseUnit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
        'name',
        'base_unit',
        'conversion_factor',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }
}
