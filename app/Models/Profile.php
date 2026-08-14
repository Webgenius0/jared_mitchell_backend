<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_online'      => 'boolean',
        'last_active_at' => 'datetime',
        'stripe_onboarded_at' => 'datetime',
        'social_links'   => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Full avatar URL — falls back to a default avatar if none set.
     */
    public function getAvatarUrlAttribute(): string
    {
        if (! $this->avatar) {
            return asset('admin/default/user.jpg');
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        if (str_starts_with($this->avatar, 'storage/') || str_starts_with($this->avatar, '/storage/')) {
            return asset(ltrim($this->avatar, '/'));
        }

        return asset('storage/' . ltrim($this->avatar, '/'));
    }
}
