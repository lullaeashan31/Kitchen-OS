<?php

namespace App\Policies;

use App\Models\DriveFile;
use App\Models\User;

class DriveFilePolicy
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
    public function view(User $user, DriveFile $driveFile): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DriveFile $driveFile): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}
