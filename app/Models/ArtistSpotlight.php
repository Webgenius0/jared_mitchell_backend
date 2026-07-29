<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Spotlight\SpotlightWeekNominee;

class ArtistSpotlight extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'user_id',

        // Identification
        'full_legal_name',
        'artist_stage_name',
        'email',
        'phone_number',
        'date_of_birth',
        'city',
        'state',
        'instagram_handle',
        'tiktok_handle',
        'facebook_url',
        'youtube_url',
        'website_portfolio_url',

        // Category
        'artist_category_id',
        'category_other_description',

        // Story
        'short_bio',
        'full_artist_story',
        'why_spotlighted',
        'community_message',
        'current_goals',

        // Media
        'headshot_path',
        'artwork_photo_paths',
        'behind_scenes_photo_path',
        'intro_video_path',

        // Consent
        'consent_public_release',
        'consent_ownership_declaration',
        'consent_interview_permission',

        // Optional
        'talent_manager_contact',
        'agent_contact',
        'press_kit_url',
        'previous_interviews',
        'awards_recognition',
        'preferred_pronouns',
        'preferred_contact_method',
        'interview_availability',

        // Submission tracking
        'status',
        'current_step',
        'submitted_at',
        'reviewed_by',
        'reviewer_notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'artwork_photo_paths' => 'array',
        'consent_public_release' => 'boolean',
        'consent_ownership_declaration' => 'boolean',
        'consent_interview_permission' => 'boolean',
        'submitted_at' => 'datetime',
        'current_step' => 'integer',
    ];

    /**
     * The user (artist role) who owns this artist spotlight.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArtistCategory::class, 'artist_category_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * The spotlight week nominees for this artist spotlight.
     */
    public function nominees(): MorphMany
    {
        return $this->morphMany(SpotlightWeekNominee::class, 'spotlightable');
    }

    /**
     * Users who liked this artist spotlight.
     */
    public function likers()
    {
        return $this->belongsToMany(User::class, 'artist_spotlight_likes', 'artist_spotlight_id', 'user_id')->withTimestamps();
    }

    /**
     * Users who bookmarked this artist spotlight.
     */
    public function bookmarkers()
    {
        return $this->belongsToMany(User::class, 'artist_spotlight_bookmarks', 'artist_spotlight_id', 'user_id')->withTimestamps();
    }

    /**
     * Shares for this artist spotlight.
     */
    public function shares()
    {
        return $this->hasMany(ArtistSpotlightShare::class);
    }
}
