<?php

namespace App\Models;

use App\Traits\CanBeFavorited;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Area extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * Register the media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->singleFile();
    }

    protected $fillable = [
        'city_id',
        'ar_name',
        'en_name',
        'status',
    ];

    protected $casts = [
        'status' => \App\Enums\GeneralStatus::class,
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function resorts(): HasMany
    {
        return $this->hasMany(Resort::class);
    }

    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(function ($q) use ($term) {
            $q->where('ar_name', 'like', "%{$term}%")
              ->orWhere('en_name', 'like', "%{$term}%");
        });
    }
}
