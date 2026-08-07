<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'starts_at', 'ends_at', 'timezone',
        'venue_name', 'address', 'city', 'state', 'hosted_by',
        'cover_image_path', 'promo_video_path', 'event_type',
        'is_spotlight_eligible', 'is_featured', 'like_count',
        'ticket_url', 'tickets_available', 'status', 'created_by'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_spotlight_eligible' => 'boolean',
        'is_featured' => 'boolean',
        'tickets_available' => 'boolean',
    ];

    public function ticketTiers()
    {
        return $this->hasMany(EventTicketTier::class)->orderBy('sort_order');
    }

    public function media()
    {
        return $this->hasMany(EventMedia::class);
    }

    public function artists()
    {
        return $this->belongsToMany(User::class, 'event_artists', 'event_id', 'artist_id')->withTimestamps();
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function sponsors()
    {
        return $this->belongsToMany(Sponsor::class, 'event_sponsor', 'event_id', 'sponsor_id')->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Users who liked this event.
     */
    public function likers()
    {
        return $this->belongsToMany(User::class, 'event_likes', 'event_id', 'user_id')->withTimestamps();
    }

    /**
     * Users who bookmarked this event.
     */
    public function bookmarkers()
    {
        return $this->belongsToMany(User::class, 'event_bookmarks', 'event_id', 'user_id')->withTimestamps();
    }

    /**
     * Shares for this event.
     */
    public function shares()
    {
        return $this->hasMany(EventShare::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($event) {
            if (!$event->slug) {
                $event->slug = Str::slug($event->title);
            }
        });
    }
}
