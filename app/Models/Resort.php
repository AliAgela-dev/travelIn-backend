<?php

namespace App\Models;

use App\Enums\ResortStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use App\Traits\HasReviews;
use App\Traits\CanBeFavorited;

class Resort extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ResortFactory> */
    use HasFactory, InteractsWithMedia, HasReviews, CanBeFavorited;

    /**
     * The relationships that should always be loaded.
     *
     * @var array<string>
     */
    protected $with = ['reviews'];

    protected $fillable = [
        'owner_id',
        'city_id',
        'area_id',
        'ar_name',
        'en_name',
        'ar_description',
        'en_description',
        'phone_number',
        'email',
        'status',
    ];

    /**
     * Register the media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ResortStatus::class,
        ];
    }

    /**
     * Boot the model and add global scope for review stats.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('withReviewStats', function (Builder $query) {
            $query->withAvg('reviews', 'rating')
                  ->withCount('reviews');
        });
    }


    /**
     * Get the owner of the resort.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the city of the resort.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the area of the resort.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the units for this resort.
     */
    public function units(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Scope a query to search resorts.
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(function ($query) use ($term) {
            $query->where('ar_name', 'like', "%{$term}%")
                ->orWhere('en_name', 'like', "%{$term}%");
        });
    }

    /**
     * Scope a query to only include active resorts.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', ResortStatus::Active);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus(Builder $query, $status): void
    {
        if (is_string($status) && $enum = ResortStatus::tryFrom($status)) {
            $status = $enum;
        }
        $query->where('status', $status);
    }

    /**
     * Scope to filter by minimum rating.
     */
    public function scopeMinRating(Builder $query, float $rating): void
    {
        $query->having('reviews_avg_rating', '>=', $rating);
    }

    /**
     * Scope to filter by city.
     */
    public function scopeInCity(Builder $query, int $cityId): void
    {
        $query->where('city_id', $cityId);
    }

    /**
     * Scope to filter by area.
     */
    public function scopeInArea(Builder $query, int $areaId): void
    {
        $query->where('area_id', $areaId);
    }
}
