<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistSpotlight extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArtistCategory::class, 'artist_category_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
