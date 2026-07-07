<?php

namespace App\Models;

use App\Models\Contest\Season;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        // Season (replaces round_session_id)
        'season_id',
        'round_session_id',  // Kept for backward compatibility during Phase 1

        // Identity
        'round_number',
        'title',
        'goal',
        'requirements',

        // Competition mechanics
        'voting_strategy',
        'submission_type',
        'submission_requirements',
        'advance_limit',
        'elimination_rule',
        'advancement_config',

        // Timeline
        'starts_at',
        'ends_at',
        'voting_ends_at',

        // State
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'advance_limit'          => 'integer',
        'sort_order'             => 'integer',
        'starts_at'              => 'datetime',
        'ends_at'                => 'datetime',
        'voting_ends_at'         => 'datetime',
        'submission_requirements'=> 'array',
        'advancement_config'     => 'array',
        'metadata'               => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The season this round belongs to.
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    /**
     * The old-style RoundSession relationship (backward compat).
     *
     * @deprecated Use season() instead.
     */
    public function roundSession(): BelongsTo
    {
        return $this->belongsTo(RoundSession::class, 'season_id');
    }

    /**
     * Submissions made in this round.
     */
    public function submissions()
    {
        return $this->hasMany(\App\Models\Contest\RoundSubmission::class);
    }

    /**
     * Votes cast in this round.
     */
    public function votes()
    {
        return $this->hasMany(\App\Models\Contest\Vote::class);
    }

    /**
     * Leaderboard entries for this round.
     */
    public function leaderboardEntries()
    {
        return $this->hasMany(\App\Models\Contest\LeaderboardEntry::class);
    }

    /**
     * Round transitions where this round is the source (from_round).
     */
    public function transitions()
    {
        return $this->hasMany(\App\Models\Contest\RoundTransition::class, 'from_round_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to currently active rounds.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to rounds that have ended and need transition processing.
     */
    public function scopeEnded($query)
    {
        return $query->where('is_active', true)
            ->where('ends_at', '<=', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Whether voting is currently open for this round.
     */
    public function isVotingOpen(): bool
    {
        $votingEnd = $this->voting_ends_at ?? $this->ends_at;

        return now()->between($this->starts_at, $votingEnd);
    }

    /**
     * Whether submissions are currently open for this round.
     */
    public function isSubmissionOpen(): bool
    {
        return now()->between($this->starts_at, $this->ends_at);
    }

    /**
     * Whether this round has ended and is ready for transition.
     */
    public function hasEnded(): bool
    {
        return $this->ends_at && now()->isAfter($this->ends_at);
    }
}
