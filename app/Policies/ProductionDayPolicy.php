<?php

namespace App\Policies;

use App\Models\ProductionDay;
use App\Models\User;

class ProductionDayPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProductionDay $productionDay): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canManageProduction();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductionDay $productionDay): bool
    {
        return $user->canManageProduction();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductionDay $productionDay): bool
    {
        return $user->isAdmin();
    }
}
