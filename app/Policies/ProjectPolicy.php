<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Staff only reach projects they're part of (as manager or assigned staff). */
    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager)) {
            return true;
        }

        return (int) $project->project_manager_id === $user->id
            || $project->users()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    public function assignStaff(User $user, Project $project): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    public function changeStatus(User $user, Project $project): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    /** Archive (soft delete). */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    public function restore(User $user, Project $project): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
