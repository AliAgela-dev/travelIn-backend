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
    public function notify(
        ?User $user,
        ?string $type,
        string $arTitle,
        string $enTitle,
        string $arBody,
        string $enBody,
        ?Model $notifiable = null,
        array $data = []
    ): Notification {
        // Create DB Notification
        $notification = Notification::create([
            'user_id' => $user?->id,
            'type' => $type,
            'ar_title' => $arTitle,
            'en_title' => $enTitle,
            'ar_body' => $arBody,
            'en_body' => $enBody,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id' => $notifiable?->id,
            'data' => $data,
        ]);

        // Send FCM Notification
        // Determine language preference (defaulting to English here, or could allow both titles/bodies in data payload)
        // For simplicity, we send English as default or based on some user setting if available.
        // Or we can send both in data payload and let the mobile app decide which to show.
        
        $title = $enTitle; // fallback or logic to choose
        $body = $enBody;

        if($user){
        $this->fcmService->sendToUser(
            $user, 
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
                array_merge([
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
            // Assume broadcast if no user_id and type is admin_broadcast
            // You might want to refine this logic if there are other types.
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
