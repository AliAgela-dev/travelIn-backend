<?php

namespace App\Traits;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait HasReviews
 * 
 * Apply this trait to any model that can BE reviewed (e.g., Resort, Unit).
 * Provides review relationships and rating calculations.
 */
trait HasReviews
{
    /**
     * Get all reviews for this model.
     */
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    /**
     * Get the average rating for this model.
     */
    public function averageRating(): ?float
    {
        $avg = $this->reviews()->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    /**
     * Get the total review count.
     */
    public function reviewCount(): int
    {
        return $this->reviews()->count();
    }

    /**
     * Get rating distribution (count per star).
     */
    public function ratingDistribution(): array
    {
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $this->reviews()->where('rating', $i)->count();
        }
        return $distribution;
    }
}
