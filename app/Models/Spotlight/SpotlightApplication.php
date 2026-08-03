<?php

namespace App\Models\Spotlight;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SpotlightApplication extends Model
{
    protected $fillable = [
        'spotlight_week_id',
        'spotlightable_type',
        'spotlightable_id',
        'user_id',
        'status',
        'applied_at',
        'reviewed_at',
        'reviewed_by',
        'reviewer_notes',
        'ai_score',
        'ai_reviewed_at',
    ];

    protected $casts = [
        'applied_at'     => 'datetime',
        'reviewed_at'    => 'datetime',
        'ai_score'       => 'float',
        'ai_reviewed_at' => 'datetime',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * The spotlight that applied (ArtistSpotlight or BusinessSpotlight).
     */
    public function spotlightable(): MorphTo
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSelected($query)
    {
        return $query->where('status', 'selected');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSelected(): bool
    {
        return $this->status === 'selected';
    }

    public function canBeWithdrawn(): bool
    {
        return in_array($this->status, ['pending']);
    }
}
