<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSpotlight extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        // Step 1 – Business Information
        'business_name',
        'owner_founder_name',
        'business_category',
        'year_founded',
        'business_website',
        'city',
        'state',

        // Step 2 – Business Story
        'business_story',
        'products_services',
        'challenges_overcome',
        'unique_factor',
        'target_customer',

        // Step 3 – Contact Information
        'email',
        'phone_number',
        'best_contact_time',
        'instagram_url',
        'tiktok_url',
        'facebook_url',
        'youtube_url',
        'google_business_profile_url',
        'linkedin_url',
        'fanbase_url',

        // Step 4 – Images
        'portrait_photo_path',
        'storefront_workspace_photo_path',
        'product_service_photo_paths',
        'team_photo_path',

        // Step 5 – Service Details
        'service_type',

        // Step 6 – Spotlight Consideration
        'why_featured',
        'growth_vision',
        'permission_feature_on_osi',
        'permission_use_submitted_photos',
        'permission_share_business_story',

        // Submission tracking
        'status',
        'current_step',
        'submitted_at',
        'reviewed_by',
        'reviewer_notes',
    ];

    protected $casts = [
        'product_service_photo_paths' => 'array',
        'permission_feature_on_osi' => 'boolean',
        'permission_use_submitted_photos' => 'boolean',
        'permission_share_business_story' => 'boolean',
        'submitted_at' => 'datetime',
        'year_founded' => 'integer',
        'current_step' => 'integer',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }
}
