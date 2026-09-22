<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Admins bypass all policy checks (PRD Section 2: "Full access").
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole(UserRole::Admin) ? true : null;
        });

        // Reports are Admin/Manager only (PRD Section 2 matrix); Staff excluded.
        Gate::define('viewReports', fn (User $user) => $user->isAdmin() || $user->isManager());
        Gate::define('exportReports', fn (User $user) => $user->isAdmin() || $user->isManager());
    }
}
