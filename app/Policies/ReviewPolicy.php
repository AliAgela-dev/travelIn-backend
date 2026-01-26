<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Determine if user can delete the review.
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * Determine if resort owner can reply to the review.
     */
    public function reply(User $user, Review $review): bool
    {
        $review->loadMissing('reviewable');
        $reviewable = $review->reviewable;

        if ($reviewable instanceof \App\Models\Resort) {
            return $reviewable->owner_id === $user->id;
        }

        if ($reviewable instanceof \App\Models\Unit) {
            $reviewable->loadMissing('resort');
            return $reviewable->resort->owner_id === $user->id;
        }

        return false;
    }
}
