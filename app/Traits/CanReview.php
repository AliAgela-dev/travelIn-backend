<?php

namespace App\Traits;

use App\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait CanReview
 * 
 * Apply this trait to User model or any model that can SUBMIT reviews.
 * Provides review submission helpers.
 */
trait CanReview
{
    /**
     * Get all reviews submitted by this user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Check if user has already reviewed a specific model.
     */
    public function hasReviewed(Model $reviewable): bool
    {
        return $this->reviews()
            ->where('reviewable_type', get_class($reviewable))
            ->where('reviewable_id', $reviewable->id)
            ->exists();
    }

    /**
     * Get user's review for a specific model.
     */
    public function getReviewFor(Model $reviewable): ?Review
    {
        return $this->reviews()
            ->where('reviewable_type', get_class($reviewable))
            ->where('reviewable_id', $reviewable->id)
            ->first();
    }
}
