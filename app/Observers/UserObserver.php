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
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type' => \App\Enums\NotificationType::Welcome->value,
                    'ar_title' => 'مرحباً بك!',
                    'en_title' => 'Welcome!',
                    'ar_body' => 'شكراً لانضمامك إلى TravelIn. استكشف أفضل المنتجعات الآن!',
                    'en_body' => 'Thank you for joining TravelIn. Explore the best resorts now!',
                    'data' => [],
                ])
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
