<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply scope if a kitchen is identified in the application context
        if (app()->has('current_kitchen')) {
            $builder->where($model->getTable() . '.kitchen_id', app('current_kitchen')->id);
        }


    }
}
