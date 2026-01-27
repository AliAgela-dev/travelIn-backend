<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    public function __construct(protected FcmService $fcmService)
    {
    }

    /**
     * Create a notification for a user.
     */
    public function notify(Notification $notification): Notification 
    {
        $title = $notification->en_title; // fallback or logic to choose
        $body = $notification->en_body;
        
        $type = $notification->type;
        $arTitle = $notification->ar_title;
        $enTitle = $notification->en_title;
        $arBody = $notification->ar_body;
        $enBody = $notification->en_body;
        $data = $notification->data ?? [];

        if($notification->user){
            $this->fcmService->sendToUser(
                $notification->user, 
                $title, 
                $body, 
                array_merge($data, [
                    'notification_id' => $notification->id,
                    'type' => $type,
                    'ar_title' => $arTitle,
                    'ar_body' => $arBody,
                    'en_title' => $enTitle,
                    'en_body' => $enBody,
                ])
            );
        }
        else{
            $this->fcmService->sendToTopic(
                'admin_broadcast',
                $title,
                $body,
                array_merge($data, [
                    'notification_id' => $notification->id,
                    'type' => $type,
                    'ar_title' => $arTitle,
                    'ar_body' => $arBody, 
                    'en_title' => $enTitle,
                    'en_body' => $enBody,
                ])
            );
        }
        return $notification;
    }

    /**
     * Notify user about booking confirmation.
     */
    public function bookingConfirmed(User $user, Model $booking): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'booking_confirmed',
            'ar_title' => 'تم تأكيد الحجز',
            'en_title' => 'Booking Confirmed',
            'ar_body' => 'تم تأكيد حجزك بنجاح. نتطلع لاستضافتك!',
            'en_body' => 'Your booking has been confirmed. We look forward to hosting you!',
            'notifiable_type' => get_class($booking),
            'notifiable_id' => $booking->id,
            'data' => [],
        ]);

        return $this->notify($notification);
    }

    /**
     * Notify user about booking rejection.
     */
    public function bookingRejected(User $user, Model $booking): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'booking_rejected',
            'ar_title' => 'تم رفض الحجز',
            'en_title' => 'Booking Rejected',
            'ar_body' => 'للأسف، تم رفض حجزك. يرجى تجربة موعد آخر.',
            'en_body' => 'Unfortunately, your booking was rejected. Please try another date.',
            'notifiable_type' => get_class($booking),
            'notifiable_id' => $booking->id,
            'data' => [],
        ]);

        return $this->notify($notification);
    }

    /**
     * Notify user about booking reminder.
     */
    public function bookingReminder(User $user, Model $booking): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'booking_reminder',
            'ar_title' => 'تذكير بالحجز',
            'en_title' => 'Booking Reminder',
            'ar_body' => 'تذكير: موعد تسجيل الوصول غداً!',
            'en_body' => 'Reminder: Your check-in is tomorrow!',
            'notifiable_type' => get_class($booking),
            'notifiable_id' => $booking->id,
            'data' => [],
        ]);

        return $this->notify($notification);
    }

    /**
     * Notify user about review reply.
     */
    public function reviewReplied(User $user, Model $review): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'review_replied',
            'ar_title' => 'رد على تقييمك',
            'en_title' => 'Reply to Your Review',
            'ar_body' => 'رد المالك على تقييمك.',
            'en_body' => 'The owner has replied to your review.',
            'notifiable_type' => get_class($review),
            'notifiable_id' => $review->id,
            'data' => [],
        ]);

        return $this->notify($notification);
    }
    /**
     * Send an existing notification via FCM without creating a new record.
     */
    public function sendThroughFcm(Notification $notification): void
    {
        $title = $notification->en_title;
        $body = $notification->en_body;
        $data = $notification->data ?? [];

        $payload = array_merge($data, [
            'notification_id' => $notification->id,
            'type' => $notification->type,
            'ar_title' => $notification->ar_title,
            'ar_body' => $notification->ar_body,
            'en_title' => $notification->en_title,
            'en_body' => $notification->en_body,
        ]);

        if ($notification->user) {
            $this->fcmService->sendToUser(
                $notification->user,
                $title,
                $body,
                $payload
            );
        } else {
            $topic = 'admin_broadcast';
            $this->fcmService->sendToTopic(
                $topic,
                $title,
                $body,
                $payload
            );
        }
    }
}
