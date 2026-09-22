<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager)) {
            return true;
        }

        return (int) $task->assigned_to === $user->id
            || (int) $task->created_by === $user->id;
    }

    /** All roles may create tasks. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Staff may update/delete only tasks assigned to them (Section 2). */
    public function update(User $user, Task $task): bool
    {
        return $user->hasRole(UserRole::Admin)
            || $user->hasRole(UserRole::Manager)
            || (int) $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
