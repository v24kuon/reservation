<?php

namespace App\Providers;

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
        Gate::define('access-dashboard', fn (User $user): bool => $user->hasPrivilegedRole());
        Gate::define('access-admin', fn (User $user): bool => $user->hasRole(User::ROLE_ADMIN));
        Gate::define('access-instructor', fn (User $user): bool => $user->hasRole(User::ROLE_INSTRUCTOR) || $user->hasRole(User::ROLE_ADMIN));
        Gate::define('manage-subscription-plans', fn (User $user): bool => $user->hasRole(User::ROLE_ADMIN));
    }
}
