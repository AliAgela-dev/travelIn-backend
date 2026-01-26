<?php

namespace App\Traits;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait CanBeFavorited
{
    /**
     * Get all favorites for this model.
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    /**
     * Get favorites count.
     */
    public function favoritesCount(): int
    {
        return $this->favorites()->count();
    }
}
