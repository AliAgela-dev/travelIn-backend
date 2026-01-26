<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Traits\CanReview;
use App\Traits\HasFavorites;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, CanReview, HasFavorites, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile();
    }

    protected $fillable = [
        'full_name',
        'phone_number',
        'password',
        'city_id',
        'date_of_birth',
        'status',
        'type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'status' => UserStatus::class,
            'type' => UserType::class,
        ];
    }

    /**
     * Get the city the user belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the resorts owned by the user.
     */
    public function resorts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Resort::class, 'owner_id');
    }

    /**
     * Get the bookings made by this user.
     */
    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    /**
     * Check if the user is banned.
     */
    public function isBanned(): bool
    {
        return $this->status === UserStatus::Banned;
    }

    /**
     * Check if the user has a specific type.
     */
    public function isType(UserType $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Ensure the user is active (not banned and not inactive).
     * Aborts with 403 if invalid.
     */
    public function ensureActive(): void
    {
        if ($this->isBanned()) {
            abort(403, 'Your account has been banned.');
        }

        if (!$this->isActive()) {
            abort(403, 'Your account is inactive.');
        }
    }

    /**
     * Scope a query to search users.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($query) use ($term) {
            $query->where('full_name', 'like', "%{$term}%")
                ->orWhere('phone_number', 'like', "%{$term}%");
        });
    }

    /**
     * Get the notifications for this user.
     */
    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications.
     */
    public function unreadNotifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->notifications()->whereNull('read_at');
    }

    /**
     * Get the FCM tokens for this user.
     */
    public function fcmTokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FcmToken::class);
    }
}
