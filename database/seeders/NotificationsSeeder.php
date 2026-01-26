<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationsSeeder extends Seeder
{
    /**
     * Seed sample notifications for users.
     */
    public function run(): void
    {
        // Get User 65 or fallback to the first traveler
        $traveler = User::find(65) ?? User::where('type', UserType::User)->first();

        if (!$traveler) {
            $this->command->warn('⚠ No travelers found. Run UsersSeeder first.');
            return;
        }

        $notificationTemplates = [
            'booking_confirmed' => [
                'ar_title' => 'تم تأكيد الحجز',
                'en_title' => 'Booking Confirmed',
                'ar_body' => 'تم تأكيد حجزك بنجاح. نتطلع لاستضافتك!',
                'en_body' => 'Your booking has been confirmed. We look forward to hosting you!',
            ],
            'booking_rejected' => [
                'ar_title' => 'تم رفض الحجز',
                'en_title' => 'Booking Rejected',
                'ar_body' => 'نأسف، تم رفض حجزك. يرجى المحاولة مرة أخرى.',
                'en_body' => 'Sorry, your booking was rejected. Please try again.',
            ],
            'booking_cancelled' => [
                'ar_title' => 'تم إلغاء الحجز',
                'en_title' => 'Booking Cancelled',
                'ar_body' => 'تم إلغاء حجزك بنجاح.',
                'en_body' => 'Your booking has been cancelled successfully.',
            ],
            'review_replied' => [
                'ar_title' => 'رد على تقييمك',
                'en_title' => 'Reply to Your Review',
                'ar_body' => 'قام المالك بالرد على تقييمك.',
                'en_body' => 'The owner has replied to your review.',
            ],
            'special_offer' => [
                'ar_title' => 'عرض خاص',
                'en_title' => 'Special Offer',
                'ar_body' => 'لا تفوت عروضنا الحصرية! خصم يصل إلى 20%',
                'en_body' => "Don't miss our exclusive offers! Up to 20% off",
            ],
            'reminder' => [
                'ar_title' => 'تذكير بالحجز',
                'en_title' => 'Booking Reminder',
                'ar_body' => 'موعد تسجيل الوصول غداً. تحقق من تفاصيل حجزك.',
                'en_body' => 'Your check-in is tomorrow. Check your booking details.',
            ],
            'welcome' => [
                'ar_title' => 'مرحباً بك!',
                'en_title' => 'Welcome!',
                'ar_body' => 'شكراً لانضمامك إلى TravelIn. استكشف أفضل المنتجعات الآن!',
                'en_body' => 'Thank you for joining TravelIn. Explore the best resorts now!',
            ],
        ];

        $count = 0;
        $types = array_keys($notificationTemplates);

        // Create 50 notifications for this single user
        for ($i = 0; $i < 50; $i++) {
            $type = $types[$i % count($types)]; // Cycle through all types
            $template = $notificationTemplates[$type];

            $data = ['type' => $type];
            $notifiableType = null;
            $notifiableId = null;

            // Link to related model based on type
            if (in_array($type, ['booking_confirmed', 'booking_rejected', 'booking_cancelled', 'reminder'])) {
                $booking = Booking::where('user_id', $traveler->id)->inRandomOrder()->first();
                if ($booking) {
                    $notifiableType = 'App\\Models\\Booking';
                    $notifiableId = $booking->id;
                    $data['booking_id'] = $booking->id;
                }
            } elseif ($type === 'review_replied') {
                $review = Review::where('user_id', $traveler->id)->whereNotNull('owner_reply')->inRandomOrder()->first();
                if ($review) {
                    $notifiableType = 'App\\Models\\Review';
                    $notifiableId = $review->id;
                    $data['review_id'] = $review->id;
                }
            }

            Notification::create([
                'user_id' => $traveler->id,
                'type' => $type,
                'ar_title' => $template['ar_title'],
                'en_title' => $template['en_title'],
                'ar_body' => $template['ar_body'],
                'en_body' => $template['en_body'],
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId,
                'data' => $data,
                'read_at' => rand(1, 100) <= 30 ? now()->subDays(rand(1, 7)) : null, // 30% read
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]);

            $count++;
        }

        $this->command->info("✓ Created {$count} notifications for user: {$traveler->full_name} (ID: {$traveler->id})");
    }
}
