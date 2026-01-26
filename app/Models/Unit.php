<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use App\Traits\HasReviews;
use App\Traits\CanBeFavorited;

class Unit extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasReviews, CanBeFavorited;

    /**
     * The relationships that should always be loaded.
     *
     * @var array<string>
     */
    protected $with = ['reviews'];

    protected $fillable = [
        'resort_id',
        'ar_name',
        'en_name',
        'ar_description',
        'en_description',
        'price_per_night',
        'capacity',
        'room_count',
        'features',
        'status',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'capacity' => 'integer',
        'room_count' => 'integer',
        'features' => 'array',
        'status' => \App\Enums\GeneralStatus::class,
    ];

    /**
     * Register the media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
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
     * Get the resort that owns the unit.
     */
    public function resort(): BelongsTo
    {
        return $this->belongsTo(Resort::class);
    }

    /**
     * Get pricing rules for this unit.
     */
    public function pricings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UnitPricing::class);
    }

    /**
     * Get blocked date ranges for this unit.
     */
    public function blockedDates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UnitAvailability::class);
    }

    /**
     * Get bookings for this unit.
     */
    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the price for a specific date (seasonal or base).
     */
    public function getPriceForDate(\Carbon\Carbon $date): float
    {
        $pricing = $this->pricings()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        return $pricing ? (float) $pricing->price_per_night : (float) $this->price_per_night;
    }

    /**
     * Check if unit is available for given date range.
     */
    public function isAvailableForDates(\Carbon\Carbon $start, \Carbon\Carbon $end): bool
    {
        return !$this->blockedDates()
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)
                          ->where('end_date', '>=', $end);
                    });
            })
            ->exists();
    }

    /**
     * Scope to filter by minimum guest capacity.
     */
    public function scopeMinCapacity(Builder $query, int $guests): void
    {
        $query->where('capacity', '>=', $guests);
    }

    /**
     * Scope to filter by price range.
     */
    public function scopePriceRange(Builder $query, ?float $min = null, ?float $max = null): void
    {
        if ($min !== null) {
            $query->where('price_per_night', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price_per_night', '<=', $max);
        }
    }

    /**
     * Scope to filter units available for given date range.
     */
    public function scopeAvailableForDates(Builder $query, string $checkIn, string $checkOut): void
    {
        $query->whereDoesntHave('blockedDates', function ($q) use ($checkIn, $checkOut) {
            $q->where('start_date', '<=', $checkOut)
              ->where('end_date', '>=', $checkIn);
        })->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
            $q->whereIn('status', ['pending', 'confirmed'])
              ->where('check_in', '<', $checkOut)
              ->where('check_out', '>', $checkIn);
        });
    }

    /**
     * Scope to filter active units.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', \App\Enums\GeneralStatus::Active);
    }

    /**
     * Scope to search units by name.
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(function ($q) use ($term) {
            $q->where('ar_name', 'like', "%{$term}%")
              ->orWhere('en_name', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to filter by city (through resort).
     */
    public function scopeInCity(Builder $query, int $cityId): void
    {
        $query->whereHas('resort', fn($q) => $q->where('city_id', $cityId));
    }

    /**
     * Scope to filter by area (through resort).
     */
    public function scopeInArea(Builder $query, int $areaId): void
    {
        $query->whereHas('resort', fn($q) => $q->where('area_id', $areaId));
    }

    /**
     * Scope to filter by minimum rating.
     */
    public function scopeMinRating(Builder $query, float $rating): void
    {
        $query->having('reviews_avg_rating', '>=', $rating);
    }
}
