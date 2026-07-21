<?php

namespace App\Models\Spotlight;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpotlightWeek extends Model
{
    protected $fillable = [
        'week_number',
        'year',
        'status',
        'voting_starts_at',
        'voting_ends_at',
        'winner_spotlightable_type',
        'winner_spotlightable_id',
        'announced_at',
        'metadata',
    ];

    protected $casts = [
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'announced_at' => 'datetime',
        'metadata' => 'array',
        'week_number' => 'integer',
        'year' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function applications(): HasMany
    {
        return $this->hasMany(SpotlightApplication::class);
    }

    public function nominees(): HasMany
    {
        return $this->hasMany(SpotlightWeekNominee::class);
    }

    /**
     * Get the winner spotlight model (ArtistSpotlight or BusinessSpotlight).
     */
    public function winner()
    {
        if (! $this->winner_spotlightable_type || ! $this->winner_spotlightable_id) {
            return null;
        }

        return $this->winner_spotlightable_type::find($this->winner_spotlightable_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeVotingOpen($query)
    {
        return $query->where('status', 'voting')
                     ->where('voting_starts_at', '<=', now())
                     ->where('voting_ends_at', '>=', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Check if voting is currently open for this week.
     */
    public function isVotingOpen(): bool
    {
        return $this->status === 'voting'
            && $this->voting_starts_at?->lte(now())
            && $this->voting_ends_at?->gte(now());
    }

    /**
     * Check if this week is accepting applications.
     */
    public function isAcceptingApplications(): bool
    {
        return in_array($this->status, ['pending', 'nominating']);
    }

    /**
     * Get the package pricing map.
     * Returns: ['starter' => ['votes' => 1, 'price' => 1.00], ...]
     */
    public static function votePricing(): array
    {
        return [
            'starter' => ['votes' => 1, 'price' => 1.00, 'label' => '$1 = 1 vote'],
            'popular' => ['votes' => 10, 'price' => 8.00, 'label' => '$8 = 10 votes'],
            'boost' => ['votes' => 25, 'price' => 18.00, 'label' => '$18 = 25 votes'],
            'power' => ['votes' => 50, 'price' => 35.00, 'label' => '$35 = 50 votes'],
        ];
    }

    /**
     * Max purchased votes allowed per nominee per week.
     */
    public static function maxPurchasedVotes(): int
    {
        return 100;
    }
}
