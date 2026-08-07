<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'website_url',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active sponsors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query sorted by name.
     */
    public function scopeSorted($query)
    {
        return $query->orderBy('name');
    }

    /**
     * The events that belong to the sponsor.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_sponsor');
    }
}
