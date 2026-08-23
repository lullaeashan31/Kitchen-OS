<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Enforces outlet scoping at the model level, not the controller — every
 * query against a scoped model automatically excludes other outlets' data
 * for a user whose permission role is outlet_manager. Super Admin, HR and
 * Accounts bypass it entirely (checked via permission, not a role-name
 * string comparison, so this stays configurable from Admin → Permissions).
 */
class OutletScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if ($user->can('bypass-outlet-scope')) {
            return;
        }

        if ($user->outlet_id) {
            $builder->where($model->getTable().'.outlet_id', $user->outlet_id);
        }
    }
}
