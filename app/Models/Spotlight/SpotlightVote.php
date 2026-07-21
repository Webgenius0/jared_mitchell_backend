<?php

namespace App\Models\Spotlight;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotlightVote extends Model
{
    protected $fillable = [
        'spotlight_week_nominee_id',
        'user_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function nominee(): BelongsTo
    {
        return $this->belongsTo(SpotlightWeekNominee::class, 'spotlight_week_nominee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForNominee($query, int $nomineeId)
    {
        return $query->where('spotlight_week_nominee_id', $nomineeId);
    }
}
