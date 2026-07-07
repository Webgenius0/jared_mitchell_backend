<?php

namespace App\Models\Contest;

use App\Models\Round;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Contestant extends Model
{
    use HasFactory;


    protected $table = 'contestants';

    protected $fillable = [
        'season_id',
        'contestable_type',
        'contestable_id',
        'display_name',
        'slug',
        'avatar_url',
        'status',
        'total_score',
        'current_round_id',
        'eliminated_in_round_id',
        'entered_at',
        'eliminated_at',
        'metadata',
    ];

    protected $casts = [
        'total_score'    => 'decimal:2',
        'entered_at'     => 'datetime',
        'eliminated_at'  => 'datetime',
        'metadata'       => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The polymorphic parent (Business, User, etc.).
     */
    public function contestable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The season this contestant belongs to.
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * The round the contestant is currently competing in.
     */
    public function currentRound(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'current_round_id');
    }

    /**
     * The round in which the contestant was eliminated (null if still active/winner).
     */
    public function eliminatedInRound(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'eliminated_in_round_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to only active (competing) contestants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to contestants by their current round.
     */
    public function scopeInRound($query, $roundId)
    {
        return $query->where('current_round_id', $roundId);
    }

    /**
     * Scope to eliminated contestants.
     */
    public function scopeEliminated($query)
    {
        return $query->whereIn('status', ['eliminated', 'disqualified']);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Whether the contestant is currently competing.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Whether the contestant has been eliminated.
     */
    public function isEliminated(): bool
    {
        return in_array($this->status, ['eliminated', 'disqualified']);
    }

    /**
     * Whether the contestant is a winner or runner-up.
     */
    public function isWinner(): bool
    {
        return in_array($this->status, ['winner', 'runner_up', 'finalist']);
    }

    /**
     * Promote this contestant to the next round.
     */
    public function advanceToRound(Round $round): void
    {
        $this->update([
            'current_round_id' => $round->id,
            'status'           => 'active',
        ]);
    }

    /**
     * Eliminate this contestant.
     */
    public function eliminate(?Round $inRound = null): void
    {
        $this->update([
            'status'                => 'eliminated',
            'eliminated_in_round_id'=> $inRound?->id ?? $this->current_round_id,
            'eliminated_at'         => now(),
        ]);
    }
}
