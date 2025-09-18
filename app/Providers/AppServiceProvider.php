<?php

namespace App\Providers;

use App\Listeners\StripeWebhookListener;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Events\WebhookReceived;

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
        // Route parameter patterns (centralized)
        Route::pattern('store', '[0-9]+');
        Route::pattern('lesson_category', '[0-9]+');
        Route::pattern('lesson', '[0-9]+');
        Route::pattern('lesson_schedule', '[0-9]+');
        Route::pattern('notification_template', '[0-9]+');

        Gate::define('access-dashboard', fn (User $user): bool => $user->hasPrivilegedRole());
        Gate::define('access-admin', fn (User $user): bool => $user->hasRole(User::ROLE_ADMIN));
        Gate::define('access-instructor', fn (User $user): bool => $user->hasRole(User::ROLE_INSTRUCTOR) || $user->hasRole(User::ROLE_ADMIN));
        Gate::define('manage-subscription-plans', fn (User $user): bool => $user->hasRole(User::ROLE_ADMIN));

        // Register Stripe webhook listener via Cashier events
        Event::listen(WebhookReceived::class, StripeWebhookListener::class);
    }
}
