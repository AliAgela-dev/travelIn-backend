<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        //
    }

    public function __construct(
        protected \App\Services\NotificationService $notificationService
    ) {}

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        // Check if owner_reply was added (was null/empty, now has value)
        if ($review->isDirty('owner_reply') && !empty($review->owner_reply) && empty($review->getOriginal('owner_reply'))) {
            $this->notificationService->reviewReplied($review->user, $review);
        }
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "restored" event.
     */
    public function restored(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
        //
    }
}
