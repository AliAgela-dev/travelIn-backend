<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class City extends Model implements HasMedia
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
        'ar_name',
        'en_name',
        'status',
    ];

    protected $casts = [
        'status' => \App\Enums\GeneralStatus::class,
    ];

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function resorts(): HasMany
    {
        return $this->hasMany(Resort::class);
    }
    
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(function ($q) use ($term) {
            $q->where('ar_name', 'like', "%{$term}%")
              ->orWhere('en_name', 'like', "%{$term}%");
        });
    }
}
