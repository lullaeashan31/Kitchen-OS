<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
