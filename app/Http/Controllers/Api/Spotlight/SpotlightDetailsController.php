<?php

namespace App\Http\Controllers\Api\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\ArtistSpotlight;
use App\Models\BusinessSpotlight;
use App\Models\Spotlight\SpotlightApplication;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SpotlightDetailsController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/spotlight/details/artist/{id}
     *
     * Get comprehensive details for an artist spotlight, including:
     * - All profile/story/media fields
     * - Category info
     * - Media URLs (headshot, artwork, behind-scenes, intro video)
     * - Interaction counts (likes, bookmarks, shares)
     * - Voting history (all weeks nominated, vote counts, ranks, winner status)
     * - Application history (all week applications)
     * - Owner/user info
     *
     * Public — no auth required.
     */
    public function artistDetails(int $id): JsonResponse
    {
        $spotlight = ArtistSpotlight::with([
            'category',
            'user.profile',
            'reviewer.profile',
        ])->withCount([
            'likers',
            'bookmarkers',
            'shares',
        ])->where('status', '!=', 'draft')
          ->find($id);

        if (! $spotlight) {
            return $this->notFound('Artist spotlight not found.');
        }

        // --- Media URLs ---
        $media = $this->formatArtistMedia($spotlight);

        // --- Interaction counts ---
        $interactions = [
            'likes_count'      => (int) $spotlight->likers_count,
            'bookmarks_count'  => (int) $spotlight->bookmarkers_count,
            'shares_count'     => (int) $spotlight->shares_count,
        ];

        // --- Voting history: all nominee entries across weeks ---
        $nominees = SpotlightWeekNominee::where('spotlightable_type', ArtistSpotlight::class)
            ->where('spotlightable_id', $spotlight->id)
            ->with(['week'])
            ->orderByDesc(
                SpotlightWeek::select('voting_starts_at')
                    ->whereColumn('id', 'spotlight_week_nominees.spotlight_week_id')
                    ->limit(1)
            )
            ->get();

        $votingHistory = $nominees->map(function ($nominee) {
            return [
                'nominee_id'  => $nominee->id,
                'week'        => $nominee->week ? [
                    'id'           => $nominee->week->id,
                    'week_number'  => $nominee->week->week_number,
                    'year'         => $nominee->week->year,
                    'status'       => $nominee->week->status,
                    'voting_starts_at' => $nominee->week->voting_starts_at,
                    'voting_ends_at'   => $nominee->week->voting_ends_at,
                ] : null,
                'rank'        => $nominee->rank,
                'is_winner'   => $nominee->is_winner,
                'votes'       => [
                    'free'  => $nominee->free_vote_count,
                    'paid'  => $nominee->paid_vote_count,
                    'total' => $nominee->total_vote_count,
                ],
            ];
        });

        // --- Voting summary ---
        $votingSummary = [
            'total_weeks_nominated' => $nominees->count(),
            'total_wins'            => $nominees->where('is_winner', true)->count(),
            'total_votes_received'  => $nominees->sum('total_vote_count'),
        ];

        // --- Application history ---
        $applications = SpotlightApplication::where('spotlightable_type', ArtistSpotlight::class)
            ->where('spotlightable_id', $spotlight->id)
            ->with(['week', 'reviewer.profile'])
            ->latest('applied_at')
            ->get()
            ->map(function ($app) {
                return [
                    'id'             => $app->id,
                    'week'           => $app->week ? [
                        'id'          => $app->week->id,
                        'week_number' => $app->week->week_number,
                        'year'        => $app->week->year,
                        'status'      => $app->week->status,
                    ] : null,
                    'status'         => $app->status,
                    'applied_at'     => $app->applied_at,
                    'reviewed_at'    => $app->reviewed_at,
                    'reviewer_notes' => $app->reviewer_notes,
                    'reviewer'       => $app->reviewer ? [
                        'id'   => $app->reviewer->id,
                        'name' => $app->reviewer->profile?->name ?? $app->reviewer->email,
                    ] : null,
                ];
            });

        // --- Owner info ---
        $owner = $spotlight->user ? [
            'id'    => $spotlight->user->id,
            'name'  => $spotlight->user->profile?->name ?? $spotlight->user->email,
            'email' => $spotlight->user->email,
        ] : null;

        // --- Category ---
        $category = $spotlight->category ? [
            'id'   => $spotlight->category->id,
            'name' => $spotlight->category->name,
        ] : [
            'id'   => null,
            'name' => $spotlight->category_other_description ?? 'Other',
        ];

        return $this->success('Artist spotlight details retrieved.', [
            'spotlight' => [
                // Basic identification
                'id'                  => $spotlight->id,
                'full_legal_name'     => $spotlight->full_legal_name,
                'artist_stage_name'   => $spotlight->artist_stage_name,
                'email'               => $spotlight->email,
                'phone_number'        => $spotlight->phone_number,
                'date_of_birth'       => $spotlight->date_of_birth,
                'city'                => $spotlight->city,
                'state'               => $spotlight->state,

                // Social & web
                'instagram_handle'    => $spotlight->instagram_handle,
                'tiktok_handle'       => $spotlight->tiktok_handle,
                'facebook_url'        => $spotlight->facebook_url,
                'youtube_url'         => $spotlight->youtube_url,
                'website_portfolio_url' => $spotlight->website_portfolio_url,

                // Category
                'category'            => $category,
                'category_other_description' => $spotlight->category_other_description,

                // Story
                'short_bio'           => $spotlight->short_bio,
                'full_artist_story'   => $spotlight->full_artist_story,
                'why_spotlighted'     => $spotlight->why_spotlighted,
                'community_message'   => $spotlight->community_message,
                'current_goals'       => $spotlight->current_goals,

                // Media
                'media'               => $media,

                // Optional extras
                'talent_manager_contact' => $spotlight->talent_manager_contact,
                'agent_contact'          => $spotlight->agent_contact,
                'press_kit_url'          => $spotlight->press_kit_url,
                'previous_interviews'    => $spotlight->previous_interviews,
                'awards_recognition'     => $spotlight->awards_recognition,
                'preferred_pronouns'     => $spotlight->preferred_pronouns,
                'preferred_contact_method' => $spotlight->preferred_contact_method,
                'interview_availability' => $spotlight->interview_availability,

                // Consent
                'consent_public_release'          => $spotlight->consent_public_release,
                'consent_ownership_declaration'   => $spotlight->consent_ownership_declaration,
                'consent_interview_permission'    => $spotlight->consent_interview_permission,

                // Status tracking
                'status'              => $spotlight->status,
                'current_step'        => $spotlight->current_step,
                'submitted_at'        => $spotlight->submitted_at,
                'reviewer_notes'      => $spotlight->reviewer_notes,
                'reviewed_by'         => $spotlight->reviewer ? [
                    'id'   => $spotlight->reviewer->id,
                    'name' => $spotlight->reviewer->profile?->name ?? $spotlight->reviewer->email,
                ] : null,

                // Owner
                'owner'               => $owner,

                // Interactions
                'interactions'        => $interactions,

                // Voting
                'voting_summary'      => $votingSummary,
                'voting_history'      => $votingHistory,

                // Applications
                'application_history' => $applications,

                // Timestamps
                'created_at'          => $spotlight->created_at,
                'updated_at'          => $spotlight->updated_at,
            ],
        ]);
    }

    /**
     * GET /api/v1/spotlight/details/business/{id}
     *
     * Get comprehensive details for a business spotlight, including:
     * - All profile/story/media fields
     * - Media URLs (portrait, storefront, product/service photos, team photo)
     * - Interaction counts (likes, bookmarks, shares)
     * - Voting history (all weeks nominated, vote counts, ranks, winner status)
     * - Application history (all week applications)
     * - Owner/user info
     *
     * Public — no auth required.
     */
    public function businessDetails(int $id): JsonResponse
    {
        $spotlight = BusinessSpotlight::with([
            'user.profile',
            'reviewer.profile',
        ])->withCount([
            'likers',
            'bookmarkers',
            'shares',
        ])->where('status', '!=', 'draft')
          ->find($id);

        if (! $spotlight) {
            return $this->notFound('Business spotlight not found.');
        }

        // --- Media URLs ---
        $media = $this->formatBusinessMedia($spotlight);

        // --- Interaction counts ---
        $interactions = [
            'likes_count'      => (int) $spotlight->likers_count,
            'bookmarks_count'  => (int) $spotlight->bookmarkers_count,
            'shares_count'     => (int) $spotlight->shares_count,
        ];

        // --- Voting history: all nominee entries across weeks ---
        $nominees = SpotlightWeekNominee::where('spotlightable_type', BusinessSpotlight::class)
            ->where('spotlightable_id', $spotlight->id)
            ->with(['week'])
            ->orderByDesc(
                SpotlightWeek::select('voting_starts_at')
                    ->whereColumn('id', 'spotlight_week_nominees.spotlight_week_id')
                    ->limit(1)
            )
            ->get();

        $votingHistory = $nominees->map(function ($nominee) {
            return [
                'nominee_id'  => $nominee->id,
                'week'        => $nominee->week ? [
                    'id'           => $nominee->week->id,
                    'week_number'  => $nominee->week->week_number,
                    'year'         => $nominee->week->year,
                    'status'       => $nominee->week->status,
                    'voting_starts_at' => $nominee->week->voting_starts_at,
                    'voting_ends_at'   => $nominee->week->voting_ends_at,
                ] : null,
                'rank'        => $nominee->rank,
                'is_winner'   => $nominee->is_winner,
                'votes'       => [
                    'free'  => $nominee->free_vote_count,
                    'paid'  => $nominee->paid_vote_count,
                    'total' => $nominee->total_vote_count,
                ],
            ];
        });

        // --- Voting summary ---
        $votingSummary = [
            'total_weeks_nominated' => $nominees->count(),
            'total_wins'            => $nominees->where('is_winner', true)->count(),
            'total_votes_received'  => $nominees->sum('total_vote_count'),
        ];

        // --- Application history ---
        $applications = SpotlightApplication::where('spotlightable_type', BusinessSpotlight::class)
            ->where('spotlightable_id', $spotlight->id)
            ->with(['week', 'reviewer.profile'])
            ->latest('applied_at')
            ->get()
            ->map(function ($app) {
                return [
                    'id'             => $app->id,
                    'week'           => $app->week ? [
                        'id'          => $app->week->id,
                        'week_number' => $app->week->week_number,
                        'year'        => $app->week->year,
                        'status'      => $app->week->status,
                    ] : null,
                    'status'         => $app->status,
                    'applied_at'     => $app->applied_at,
                    'reviewed_at'    => $app->reviewed_at,
                    'reviewer_notes' => $app->reviewer_notes,
                    'reviewer'       => $app->reviewer ? [
                        'id'   => $app->reviewer->id,
                        'name' => $app->reviewer->profile?->name ?? $app->reviewer->email,
                    ] : null,
                ];
            });

        // --- Owner info ---
        $owner = $spotlight->user ? [
            'id'    => $spotlight->user->id,
            'name'  => $spotlight->user->profile?->name ?? $spotlight->user->email,
            'email' => $spotlight->user->email,
        ] : null;

        return $this->success('Business spotlight details retrieved.', [
            'spotlight' => [
                // Basic identification
                'id'                  => $spotlight->id,
                'business_name'       => $spotlight->business_name,
                'owner_founder_name'  => $spotlight->owner_founder_name,
                'business_category'   => $spotlight->business_category,
                'year_founded'        => $spotlight->year_founded,
                'business_website'    => $spotlight->business_website,
                'city'                => $spotlight->city,
                'state'               => $spotlight->state,

                // Contact
                'email'               => $spotlight->email,
                'phone_number'        => $spotlight->phone_number,
                'best_contact_time'   => $spotlight->best_contact_time,

                // Social & web
                'instagram_url'       => $spotlight->instagram_url,
                'tiktok_url'          => $spotlight->tiktok_url,
                'facebook_url'        => $spotlight->facebook_url,
                'youtube_url'         => $spotlight->youtube_url,
                'google_business_profile_url' => $spotlight->google_business_profile_url,
                'linkedin_url'        => $spotlight->linkedin_url,
                'fanbase_url'         => $spotlight->fanbase_url,

                // Story
                'business_story'      => $spotlight->business_story,
                'products_services'   => $spotlight->products_services,
                'challenges_overcome' => $spotlight->challenges_overcome,
                'unique_factor'       => $spotlight->unique_factor,
                'target_customer'     => $spotlight->target_customer,

                // Media
                'media'               => $media,

                // Service details
                'service_type'        => $spotlight->service_type,

                // Spotlight consideration
                'why_featured'                    => $spotlight->why_featured,
                'growth_vision'                   => $spotlight->growth_vision,
                'permission_feature_on_osi'       => $spotlight->permission_feature_on_osi,
                'permission_use_submitted_photos' => $spotlight->permission_use_submitted_photos,
                'permission_share_business_story' => $spotlight->permission_share_business_story,

                // Status tracking
                'status'              => $spotlight->status,
                'current_step'        => $spotlight->current_step,
                'submitted_at'        => $spotlight->submitted_at,
                'reviewer_notes'      => $spotlight->reviewer_notes,
                'reviewed_by'         => $spotlight->reviewer ? [
                    'id'   => $spotlight->reviewer->id,
                    'name' => $spotlight->reviewer->profile?->name ?? $spotlight->reviewer->email,
                ] : null,

                // Owner
                'owner'               => $owner,

                // Interactions
                'interactions'        => $interactions,

                // Voting
                'voting_summary'      => $votingSummary,
                'voting_history'      => $votingHistory,

                // Applications
                'application_history' => $applications,

                // Timestamps
                'created_at'          => $spotlight->created_at,
                'updated_at'          => $spotlight->updated_at,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Format all artist media paths into full URLs.
     */
    private function formatArtistMedia(ArtistSpotlight $spotlight): array
    {
        $images = [];

        if ($spotlight->headshot_path) {
            $images['headshot'] = $this->formatImageUrl($spotlight->headshot_path);
        }

        if ($spotlight->artwork_photo_paths && is_array($spotlight->artwork_photo_paths)) {
            $images['artwork_photos'] = array_values(
                array_filter(array_map([$this, 'formatImageUrl'], $spotlight->artwork_photo_paths))
            );
        } else {
            $images['artwork_photos'] = [];
        }

        if ($spotlight->behind_scenes_photo_path) {
            $images['behind_scenes_photo'] = $this->formatImageUrl($spotlight->behind_scenes_photo_path);
        }

        if ($spotlight->intro_video_path) {
            $images['intro_video'] = $this->formatImageUrl($spotlight->intro_video_path);
        }

        return $images;
    }

    /**
     * Format all business media paths into full URLs.
     */
    private function formatBusinessMedia(BusinessSpotlight $spotlight): array
    {
        $images = [];

        if ($spotlight->portrait_photo_path) {
            $images['portrait_photo'] = $this->formatImageUrl($spotlight->portrait_photo_path);
        }

        if ($spotlight->storefront_workspace_photo_path) {
            $images['storefront_workspace_photo'] = $this->formatImageUrl($spotlight->storefront_workspace_photo_path);
        }

        if ($spotlight->product_service_photo_paths && is_array($spotlight->product_service_photo_paths)) {
            $images['product_service_photos'] = array_values(
                array_filter(array_map([$this, 'formatImageUrl'], $spotlight->product_service_photo_paths))
            );
        } else {
            $images['product_service_photos'] = [];
        }

        if ($spotlight->team_photo_path) {
            $images['team_photo'] = $this->formatImageUrl($spotlight->team_photo_path);
        }

        return $images;
    }

    /**
     * Convert a storage path or URL to a public URL.
     *
     * Handles paths that already include the 'storage/' prefix
     * (as stored by FileHandle helper) by stripping it before
     * passing to Storage::url() to avoid duplication.
     */
    private function formatImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Remove 'storage/' prefix if present since Storage::disk('public')->url() already adds it
        $path = preg_replace('#^storage/#', '', $path);

        return Storage::disk('public')->url($path);
    }
}
