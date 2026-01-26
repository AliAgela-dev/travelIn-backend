<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function __construct(
        protected \App\Services\NotificationService $notificationService
    ) {}

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Send welcome notification to new travelers
        if ($user->type === \App\Enums\UserType::User) {
            $this->notificationService->notify(
                $user,
                \App\Enums\NotificationType::Welcome->value,
                'مرحباً بك!',
                'Welcome!',
                'شكراً لانضمامك إلى TravelIn. استكشف أفضل المنتجعات الآن!',
                'Thank you for joining TravelIn. Explore the best resorts now!'
            );
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
