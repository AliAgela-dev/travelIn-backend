<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Create a notification for a user.
     */
    public function notify(
        User $user,
        string $type,
        string $arTitle,
        string $enTitle,
        string $arBody,
        string $enBody,
        ?Model $notifiable = null,
        array $data = []
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'ar_title' => $arTitle,
            'en_title' => $enTitle,
            'ar_body' => $arBody,
            'en_body' => $enBody,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable?->id,
            'data' => $data,
        ]);
    }

    /**
     * Notify user about booking confirmation.
     */
    public function bookingConfirmed(User $user, Model $booking): Notification
    {
        return $this->notify(
            $user,
            'booking_confirmed',
            'تم تأكيد الحجز',
            'Booking Confirmed',
            'تم تأكيد حجزك بنجاح. نتطلع لاستضافتك!',
            'Your booking has been confirmed. We look forward to hosting you!',
            $booking
        );
    }

    /**
     * Notify user about booking rejection.
     */
    public function bookingRejected(User $user, Model $booking): Notification
    {
        return $this->notify(
            $user,
            'booking_rejected',
            'تم رفض الحجز',
            'Booking Rejected',
            'للأسف، تم رفض حجزك. يرجى تجربة موعد آخر.',
            'Unfortunately, your booking was rejected. Please try another date.',
            $booking
        );
    }

    /**
     * Notify user about booking reminder.
     */
    public function bookingReminder(User $user, Model $booking): Notification
    {
        return $this->notify(
            $user,
            'booking_reminder',
            'تذكير بالحجز',
            'Booking Reminder',
            'تذكير: موعد تسجيل الوصول غداً!',
            'Reminder: Your check-in is tomorrow!',
            $booking
        );
    }

    /**
     * Notify user about review reply.
     */
    public function reviewReplied(User $user, Model $review): Notification
    {
        return $this->notify(
            $user,
            'review_replied',
            'رد على تقييمك',
            'Reply to Your Review',
            'رد المالك على تقييمك.',
            'The owner has replied to your review.',
            $review
        );
    }
}
