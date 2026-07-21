<?php

namespace App\Models\Spotlight;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpotlightVotePackage extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'votes_count',
        'price',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'votes_count' => 'integer',
        'price'       => 'decimal:2',
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function purchases(): HasMany
    {
        return $this->hasMany(SpotlightVotePurchase::class, 'spotlight_vote_package_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the formatted label shown to users.
     */
    public function getLabelAttribute(): string
    {
        return "{$this->name} — {$this->votes_count} vote(s) at \${$this->price}";
    }

    /**
     * Get the price in cents for Stripe.
     */
    public function getPriceInCentsAttribute(): int
    {
        return (int) round($this->price * 100);
    }

    /**
     * Find a package by its slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->active()->first();
    }
}
