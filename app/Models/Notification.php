<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'ar_title',
        'en_title',
        'ar_body',
        'en_body',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'status' => \App\Enums\NotificationStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): void
    {
        $query->whereNotNull('read_at');
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', \App\Enums\NotificationStatus::Pending);
    }

    public function scopeSent(Builder $query): void
    {
        $query->where('status', \App\Enums\NotificationStatus::Sent);
    }
}
