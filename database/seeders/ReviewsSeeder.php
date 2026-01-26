<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Resort;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewsSeeder extends Seeder
{
    /**
     * Seed reviews for completed bookings (units) and resorts.
     */
    public function run(): void
    {
        $completedBookings = Booking::where('status', BookingStatus::Completed)
            ->with(['unit.resort', 'user'])
            ->get();

        if ($completedBookings->isEmpty()) {
            $this->command->warn('⚠ No completed bookings found. Run BookingsSeeder first.');
            return;
        }

        $reviewComments = [
            5 => [
                'Excellent experience! The place was spotless and the host was very responsive.',
                'Amazing stay! Exceeded all expectations. Will definitely come back.',
                'Perfect location, wonderful amenities. Highly recommend!',
            ],
            4 => [
                'Great place overall. Minor issues but nothing major.',
                'Very good stay. The view was spectacular.',
                'Nice and clean. Good value for money.',
            ],
            3 => [
                'Decent stay. Room for improvement in cleanliness.',
                'Average experience. Location was good but facilities need updating.',
            ],
        ];

        $resortComments = [
            5 => [
                'Outstanding resort! Amazing facilities and wonderful staff.',
                'Best resort experience ever! Everything was perfect.',
                'Incredible resort with beautiful grounds and excellent service.',
            ],
            4 => [
                'Great resort with nice amenities. Staff was helpful.',
                'Very good resort. Clean common areas and good location.',
                'Enjoyed our stay at this resort. Would recommend.',
            ],
            3 => [
                'Decent resort. Some facilities need maintenance.',
                'Average resort. Location is convenient but could be cleaner.',
            ],
        ];

        $ownerReplies = [
            'Thank you for your kind words! We hope to see you again soon.',
            'We appreciate your feedback and look forward to hosting you again.',
            'Thank you for staying with us! Your feedback helps us improve.',
        ];

        $unitCount = 0;
        $resortCount = 0;
        $reviewedResorts = []; // Track which user-resort combinations have been reviewed

        foreach ($completedBookings as $booking) {
            // 60% chance of leaving a unit review
            if (rand(1, 100) <= 60) {
                $rating = fake()->randomElement([3, 4, 4, 5, 5, 5]);
                $comments = $reviewComments[$rating] ?? $reviewComments[4];

                Review::create([
                    'user_id' => $booking->user_id,
                    'reviewable_type' => 'App\\Models\\Unit',
                    'reviewable_id' => $booking->unit_id,
                    'rating' => $rating,
                    'comment' => fake()->randomElement($comments),
                    'owner_reply' => rand(1, 100) <= 70 ? fake()->randomElement($ownerReplies) : null,
                ]);

                $unitCount++;

                // 50% chance of also leaving a resort review (if not already reviewed by this user)
                $resortId = $booking->unit->resort_id;
                $userResortKey = $booking->user_id . '-' . $resortId;

                if (rand(1, 100) <= 50 && !isset($reviewedResorts[$userResortKey])) {
                    $resortRating = fake()->randomElement([3, 4, 4, 5, 5, 5]);
                    $resortCommentsList = $resortComments[$resortRating] ?? $resortComments[4];

                    Review::create([
                        'user_id' => $booking->user_id,
                        'reviewable_type' => 'App\\Models\\Resort',
                        'reviewable_id' => $resortId,
                        'rating' => $resortRating,
                        'comment' => fake()->randomElement($resortCommentsList),
                        'owner_reply' => rand(1, 100) <= 60 ? fake()->randomElement($ownerReplies) : null,
                    ]);

                    $reviewedResorts[$userResortKey] = true;
                    $resortCount++;
                }
            }
        }

        $this->command->info("✓ Created {$unitCount} unit reviews and {$resortCount} resort reviews (some with owner replies)");

        // Specific seeding for User 65
        $targetUser = User::find(65);
        if ($targetUser) {
            $this->command->info("Creating specific reviews for User ID 65...");
            
            // Get some random units and resorts
            $units = \App\Models\Unit::inRandomOrder()->limit(5)->get();
            $resorts = \App\Models\Resort::inRandomOrder()->limit(3)->get();

            foreach ($units as $unit) {
                Review::factory()->create([
                    'user_id' => $targetUser->id,
                    'reviewable_type' => 'App\\Models\\Unit',
                    'reviewable_id' => $unit->id,
                    'rating' => fake()->randomElement([4, 5]),
                    'comment' => "Specific review from User 65: " . fake()->sentence(),
                    'owner_reply' => fake()->boolean(60) ? fake()->sentence() : null,
                ]);
            }

            foreach ($resorts as $resort) {
                Review::factory()->create([
                    'user_id' => $targetUser->id,
                    'reviewable_type' => 'App\\Models\\Resort',
                    'reviewable_id' => $resort->id,
                    'rating' => fake()->randomElement([4, 5]),
                    'comment' => "Resort review from User 65: " . fake()->sentence(),
                    'owner_reply' => fake()->boolean(70) ? fake()->sentence() : null,
                ]);
            }
            $this->command->info("✓ Added specific reviews for User 65.");
        } else {
            $this->command->warn("Target User 65 not found for specific review seeding.");
        }
    }
}
