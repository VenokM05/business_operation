<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Staff only reach requests assigned to or created by them. */
    public function view(User $user, ServiceRequest $request): bool
    {
        if ($user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager)) {
            return true;
        }

        return (int) $request->assigned_to === $user->id
            || (int) $request->created_by === $user->id;
    }

    /** All roles may raise a service request (PRD Section 2 matrix). */
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ServiceRequest $request): bool
    {
        return $user->hasRole(UserRole::Admin)
            || $user->hasRole(UserRole::Manager)
            || (int) $request->assigned_to === $user->id
            || (int) $request->created_by === $user->id;
    }

    public function assignStaff(User $user, ServiceRequest $request): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    /** Staff may transition their own requests; the workflow rules are enforced separately. */
    public function changeStatus(User $user, ServiceRequest $request): bool
    {
        return $this->update($user, $request);
    }

    /** Archive (soft delete): managers and up. */
    public function delete(User $user, ServiceRequest $request): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    public function restore(User $user, ServiceRequest $request): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    public function forceDelete(User $user, ServiceRequest $request): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
