<?php

namespace App\Listeners;

use App\Events\ReviewReplied;
use App\Services\NotificationService;

class SendReviewRepliedNotification
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function handle(ReviewReplied $event): void
    {
        $this->notificationService->reviewReplied($event->review->user, $event->review);
    }
}
