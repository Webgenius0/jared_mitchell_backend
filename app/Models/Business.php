<?php

namespace App\Models;

use App\Contracts\Contestable;
use App\Models\Contest\Contestant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model implements Contestable
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'business_name',
        'owner_founder_name',
        'story',
        'mission',
        'website_social_media',
        'community_impact_statement',
        'revenue_stage',
        'why_they_deserve_to_compete',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }

    /**
     * All uploaded media files for this business.
     */
    public function media()
    {
        return $this->hasMany(BusinessMedia::class);
    }

    public function interactions()
    {
        return $this->hasMany(BusinessInteraction::class);
    }

    public function claps()
    {
        return $this->interactions()->where('action_type', 'clap');
    }

    /*
    |--------------------------------------------------------------------------
    | Contestable Interface Implementation
    |--------------------------------------------------------------------------
    */

    /**
     * Get the display name for contest leaderboards.
     */
    public function getContestantName(): string
    {
        return $this->business_name ?? $this->owner_founder_name ?? 'Unknown Business';
    }

    /**
     * Get the avatar URL for contest cards.
     * Returns the raw file path. The API layer should generate
     * the appropriate URL (temporary signed or public).
     */
    public function getContestantAvatar(): ?string
    {
        $firstMedia = $this->media()->first();

        return $firstMedia?->file_path;
    }

    /**
     * Contestant records for this business across all seasons.
     */
    public function contestants(): MorphMany
    {
        return $this->morphMany(Contestant::class, 'contestable');
    }
}
