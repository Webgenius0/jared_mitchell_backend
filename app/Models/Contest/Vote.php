<?php

namespace App\Models\Contest;

use App\Models\Round;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Vote extends Model
{
    protected $table = 'votes';

    protected $fillable = [
        'user_id',
        'round_id',
        'votable_type',
        'votable_id',
        'vote_type',
        'weight',
        'category',
        'metadata',
    ];

    protected $casts = [
        'weight'   => 'decimal:2',
        'metadata' => 'array',
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

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * The entity this vote was cast for (Contestant, Business, etc.).
     */
    public function votable(): MorphTo
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeInRound($query, int $roundId)
    {
        return $query->where('round_id', $roundId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForEntity($query, string $type, int $id)
    {
        return $query->where('votable_type', $type)
                     ->where('votable_id', $id);
    }

    public function scopeUpvotes($query)
    {
        return $query->where('vote_type', 'upvote');
    }

    public function scopeDownvotes($query)
    {
        return $query->where('vote_type', 'downvote');
    }
}
