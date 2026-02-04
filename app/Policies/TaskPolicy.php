<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
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
    public function view(User $user, Task $task): bool
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
    public function update(User $user, Task $task): bool
    {
        return $user->canManageProduction();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->canManageProduction();
    }

    /**
     * Determine whether the user can mark task as complete.
     */
    public function complete(User $user, Task $task): bool
    {
        // Assigned user can complete
        if ($task->assigned_to === $user->id) {
            return true;
        }

        // Managers and Admins can complete any task
        return $user->canManageProduction();
    }
}
