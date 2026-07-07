<?php

namespace App\Models\Contest;

use App\Models\Round;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoundTransition extends Model
{
    use HasFactory;


    protected $table = 'round_transitions';

    protected $fillable = [
        'from_round_id',
        'to_round_id',
        'season_id',
        'status',
        'elimination_rule',
        'transition_config',
        'total_contestants',
        'advanced_count',
        'eliminated_count',
        'advanced_contestants',
        'eliminated_contestants',
        'metadata',
        'processed_at',
    ];

    protected $casts = [
        'transition_config'      => 'array',
        'advanced_contestants'   => 'array',
        'eliminated_contestants' => 'array',
        'metadata'               => 'array',
        'processed_at'           => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function fromRound(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'from_round_id');
    }

    public function toRound(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'to_round_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
