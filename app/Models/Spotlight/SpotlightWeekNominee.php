<?php

namespace App\Models\Spotlight;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SpotlightWeekNominee extends Model
{
    protected $fillable = [
        'spotlight_week_id',
        'spotlightable_type',
        'spotlightable_id',
        'user_id',
        'free_vote_count',
        'paid_vote_count',
        'total_vote_count',
        'rank',
        'is_winner',
    ];

    protected $casts = [
        'free_vote_count'  => 'integer',
        'paid_vote_count'  => 'integer',
        'total_vote_count' => 'integer',
        'rank'             => 'integer',
        'is_winner'        => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function week(): BelongsTo
    {
        return $this->belongsTo(SpotlightWeek::class, 'spotlight_week_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The spotlight being nominated (ArtistSpotlight or BusinessSpotlight).
     */
    public function spotlightable(): MorphTo
    {
        return $this->morphTo();
    }

    public function freeVotes(): HasMany
    {
        return $this->hasMany(SpotlightVote::class);
    }

    public function votePurchases(): HasMany
    {
        return $this->hasMany(SpotlightVotePurchase::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWinners($query)
    {
        return $query->where('is_winner', true);
    }

    public function scopeOrderedByVotes($query)
    {
        return $query->orderByDesc('total_vote_count');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Increment free vote count atomically and update total.
     */
    public function incrementFreeVotes(): void
    {
        $this->increment('free_vote_count');
        $this->increment('total_vote_count');
    }

    /**
     * Decrement free vote count atomically and update total.
     */
    public function decrementFreeVotes(): void
    {
        $this->decrement('free_vote_count');
        $this->decrement('total_vote_count');
    }

    /**
     * Add paid votes atomically (after purchase approval).
     */
    public function addPaidVotes(int $count): void
    {
        $this->increment('paid_vote_count', $count);
        $this->increment('total_vote_count', $count);
    }

    /**
     * Remove paid votes (on refund).
     */
    public function removePaidVotes(int $count): void
    {
        $this->decrement('paid_vote_count', $count);
        $this->decrement('total_vote_count', $count);
    }

    /**
     * Check if this nominee has reached the 100 paid-vote cap.
     */
    public function hasReachedPaidVoteCap(): bool
    {
        return $this->paid_vote_count >= SpotlightWeek::maxPurchasedVotes();
    }

    /**
     * Remaining paid votes that can still be purchased.
     */
    public function remainingPaidVoteSlots(): int
    {
        return max(0, SpotlightWeek::maxPurchasedVotes() - $this->paid_vote_count);
    }
}
