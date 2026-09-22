<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /** Everyone authenticated can reach the (scoped) index listing. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Admin/manager see all clients; staff only clients tied to their assignments
     * (a project they belong to, or a request assigned to / created by them).
     */
    public function view(User $user, Client $client): bool
    {
        if ($user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager)) {
            return true;
        }

        return Client::visibleTo($user)->whereKey($client->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    public function update(User $user, Client $client): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    /** Archive (soft delete). */
    public function delete(User $user, Client $client): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    public function restore(User $user, Client $client): bool
    {
        return $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Manager);
    }

    /** Force delete is Admin-only (Gate short-circuits admins, kept for clarity). */
    public function forceDelete(User $user, Client $client): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
