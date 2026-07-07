<?php

namespace App\Models\Contest;

use App\Models\Round;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderboardEntry extends Model
{
    use HasFactory;


    protected $table = 'leaderboard_entries';

    protected $fillable = [
        'season_id',
        'round_id',
        'contestant_id',
        'rank',
        'total_score',
        'votes_count',
        'avg_score',
        'snapshot',
        'calculated_at',
    ];

    protected $casts = [
        'total_score'   => 'decimal:2',
        'avg_score'     => 'decimal:2',
        'snapshot'      => 'array',
        'calculated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function contestant(): BelongsTo
    {
        return $this->belongsTo(Contestant::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForRound($query, int $roundId)
    {
        return $query->where('round_id', $roundId);
    }

    public function scopeForSeason($query, int $seasonId)
    {
        return $query->where('season_id', $seasonId);
    }

    public function scopeOverall($query)
    {
        return $query->whereNull('round_id');
    }

    public function scopeRanked($query)
    {
        return $query->orderBy('rank');
    }
}
