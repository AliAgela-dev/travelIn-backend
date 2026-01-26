<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
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
        // Observers
        \App\Models\City::observe(\App\Observers\CityObserver::class);
        \App\Models\Area::observe(\App\Observers\AreaObserver::class);
        \App\Models\Booking::observe(\App\Observers\BookingObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Review::observe(\App\Observers\ReviewObserver::class);

        // Event Listeners
        Event::listen(\App\Events\BookingConfirmed::class, \App\Listeners\SendBookingConfirmedNotification::class);
        Event::listen(\App\Events\BookingRejected::class, \App\Listeners\SendBookingRejectedNotification::class);
        Event::listen(\App\Events\ReviewReplied::class, \App\Listeners\SendReviewRepliedNotification::class);

        // Policies
        Gate::policy(\App\Models\Booking::class, \App\Policies\BookingPolicy::class);
        Gate::policy(\App\Models\Notification::class, \App\Policies\NotificationPolicy::class);
        Gate::policy(\App\Models\Favorite::class, \App\Policies\FavoritePolicy::class);
        Gate::policy(\App\Models\Review::class, \App\Policies\ReviewPolicy::class);

        // Admin Gates
        Gate::define('viewDashboard', [\App\Policies\AdminPolicy::class, 'viewDashboard']);
        Gate::define('isAdmin', [\App\Policies\AdminPolicy::class, 'isAdmin']);
        Gate::define('isSuperAdmin', [\App\Policies\AdminPolicy::class, 'isSuperAdmin']);
    }
}
