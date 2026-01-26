<?php

namespace App\Traits;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasFavorites
{
    /**
     * Get all favorites for this user.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Check if user has favorited a model.
     */
    public function hasFavorited(Model $model): bool
    {
        return $this->favorites()
            ->where('favoritable_type', get_class($model))
            ->where('favoritable_id', $model->id)
            ->exists();
    }

    /**
     * Add a model to favorites.
     */
    public function favorite(Model $model): Favorite
    {
        return $this->favorites()->updateOrCreate([
            'favoritable_type' => get_class($model),
            'favoritable_id' => $model->id,
        ]);
    }

    /**
     * Remove a model from favorites.
     */
    public function unfavorite(Model $model): bool
    {
        return $this->favorites()
            ->where('favoritable_type', get_class($model))
            ->where('favoritable_id', $model->id)
            ->delete() > 0;
    }
}
