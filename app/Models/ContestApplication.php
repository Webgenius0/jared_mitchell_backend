<?php

namespace App\Models;

use App\Models\Contest\Season;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'season_id',
        'status',
        'ai_score',
        'ai_reviewed_at',
        'ai_verdict',
        'ai_confidence',
        'approved_at',
        'approved_by',
        'admin_note',
        'rejected_reason',
        'metadata',
    ];

    protected $casts = [
        'approved_at'     => 'datetime',
        'ai_reviewed_at'  => 'datetime',
        'ai_score'        => 'float',
        'ai_confidence'   => 'float',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'metadata'        => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The business that applied.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * The season this application belongs to.
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    /**
     * The admin who approved/reviewed this application.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to applications pending review (including AI review).
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'ai_review', 'needs_review']);
    }

    /**
     * Scope to applications flagged for admin review.
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('status', 'needs_review');
    }

    /**
     * Scope to approved applications only.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Whether this application is pending AI or admin review.
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'ai_review', 'needs_review']);
    }

    /**
     * Whether AI has finished reviewing this application.
     */
    public function isAiReviewed(): bool
    {
        return $this->ai_reviewed_at !== null;
    }

    /**
     * Whether this application was auto-approved or auto-rejected by AI.
     */
    public function wasAutoProcessed(): bool
    {
        return $this->isAiReviewed()
            && $this->ai_confidence !== null
            && $this->ai_confidence >= 0.85;
    }
}
