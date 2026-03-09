<?php

namespace App\Traits;

use App\Models\Kitchen;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant()
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (app()->has('current_kitchen') && !$model->kitchen_id) {
                $model->kitchen_id = app('current_kitchen')->id;
            }
        });
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }
}
