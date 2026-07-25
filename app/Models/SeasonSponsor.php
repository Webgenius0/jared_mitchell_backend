<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeasonSponsor extends Model
{
    protected $table = 'season_sponsor';

    protected $fillable = [
        'season_id',
        'sponsor_id',
    ];

    /**
     * The season this pivot belongs to.
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Contest\Season::class, 'season_id');
    }

    /**
     * The sponsor this pivot belongs to.
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class, 'sponsor_id');
    }
}
