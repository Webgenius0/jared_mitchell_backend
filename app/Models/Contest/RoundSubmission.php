<?php

namespace App\Models\Contest;

use App\Models\Round;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoundSubmission extends Model
{
    protected $table = 'round_submissions';

    protected $fillable = [
        'contestant_id',
        'round_id',
        'title',
        'description',
        'media_urls',
        'status',
        'score',
        'submitted_at',
        'metadata',
    ];

    protected $casts = [
        'media_urls'   => 'array',
        'score'        => 'decimal:2',
        'submitted_at' => 'datetime',
        'metadata'     => 'array',
    ];

    protected $appends = ['media_full_urls'];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Return media_urls as full absolute URLs.
     */
    public function getMediaFullUrlsAttribute(): ?array
    {
        if (empty($this->media_urls)) {
            return null;
        }

        return array_map(fn($path) => url($path), $this->media_urls);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function contestant(): BelongsTo
    {
        return $this->belongsTo(Contestant::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeInRound($query, int $roundId)
    {
        return $query->where('round_id', $roundId);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'approved']);
    }

    public function submit(): void
    {
        $this->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    public function approve(): void
    {
        $this->update(['status' => 'approved']);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }
}
