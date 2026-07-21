<?php

namespace App\Http\Controllers\Api\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\ArtistSpotlight;
use App\Models\BusinessSpotlight;
use App\Models\Spotlight\SpotlightApplication;
use App\Models\Spotlight\SpotlightWeek;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SpotlightApplicationController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/spotlight/weeks/open
     *
     * List all weeks currently accepting applications.
     * Authenticated users can see which weeks they can apply to.
     */
    public function openWeeks(): JsonResponse
    {
        $weeks = SpotlightWeek::whereIn('status', ['pending', 'nominating'])
            ->latest('voting_starts_at')
            ->get()
            ->map(fn($week) => [
                'id' => $week->id,
                'week_number' => $week->week_number,
                'year' => $week->year,
                'status' => $week->status,
                'is_accepting_applications' => $week->isAcceptingApplications(),
                'voting_starts_at' => $week->voting_starts_at,
                'voting_ends_at' => $week->voting_ends_at,
            ]);

        return $this->success('Open spotlight weeks retrieved.', ['weeks' => $weeks]);
    }

    /**
     * POST /api/v1/spotlight/weeks/{week}/apply
     *
     * Spotlight owner applies their spotlight to a weekly voting cycle.
     *
     * @bodyParam spotlightable_type string required "artist" or "business"
     * @bodyParam spotlightable_id   int    required ID of the ArtistSpotlight or BusinessSpotlight
     */
    public function apply(Request $request, SpotlightWeek $week): JsonResponse
    {
        if (! $week->isAcceptingApplications()) {
            return $this->error(null, 'This week is no longer accepting applications.', 422);
        }

        $validated = $request->validate([
            'spotlightable_type' => ['required', 'string', Rule::in(['artist', 'business'])],
            'spotlightable_id' => ['required', 'integer'],
        ]);

        $user = auth()->user();

        // Resolve and validate spotlight ownership
        $spotlight = $this->resolveSpotlight(
            $validated['spotlightable_type'],
            $validated['spotlightable_id']
        );

        if (! $spotlight) {
            return $this->notFound('Spotlight not found.');
        }

        // Check ownership via email or user association
        if (! $this->userOwnsSpotlight($user, $spotlight, $validated['spotlightable_type'])) {
            return $this->forbidden('You do not own this spotlight.');
        }

        // Check spotlight is approved
        if (! in_array($spotlight->status ?? '', ['approved', 'featured'])) {
            return $this->error(null, 'Only approved spotlights can apply to weekly voting.', 422);
        }

        // Morphable type map
        $morphType = $this->getMorphType($validated['spotlightable_type']);

        // Check duplicate application
        $existing = SpotlightApplication::where('spotlight_week_id', $week->id)
            ->where('spotlightable_type', $morphType)
            ->where('spotlightable_id', $spotlight->id)
            ->first();

        if ($existing) {
            return $this->error(
                null,
                'This spotlight has already applied to this week. Status: ' . $existing->status,
                409
            );
        }

        $application = SpotlightApplication::create([
            'spotlight_week_id' => $week->id,
            'spotlightable_type' => $morphType,
            'spotlightable_id' => $spotlight->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'applied_at' => now(),
        ]);

        return $this->success('Application submitted successfully.', [
            'application' => $application->load('week'),
        ], 201);
    }

    /**
     * POST /api/v1/spotlight/applications/{application}/withdraw
     *
     * Spotlight owner withdraws their application (only if still pending).
     */
    public function withdraw(Request $request, SpotlightApplication $application): JsonResponse
    {
        $user = auth('api')->user();

        if ($application->user_id !== $user->id) {
            return $this->forbidden('You do not own this application.');
        }

        if (! $application->canBeWithdrawn()) {
            return $this->error(
                null,
                "Cannot withdraw an application with status: {$application->status}",
                422
            );
        }

        $application->update(['status' => 'withdrawn']);

        return $this->success('Application withdrawn successfully.');
    }

    /**
     * GET /api/v1/spotlight/my-applications
     *
     * Get all applications submitted by the authenticated user.
     * Returns a clean paginated response with only essential fields.
     */
    public function myApplications(Request $request): JsonResponse
    {
        $user = auth()->user();

        $applications = SpotlightApplication::where('user_id', $user->id)
            ->with('week', 'spotlightable')
            ->latest()
            ->paginate(15);

        $data = $applications->map(function ($app) {
            $spotlight = $app->spotlightable;
            $isArtist = $app->spotlightable_type === ArtistSpotlight::class;

            return [
                'id'              => $app->id,
                'spotlight_week_id' => $app->spotlight_week_id,
                'status'          => $app->status,
                'applied_at'      => $app->applied_at,
                'reviewed_at'     => $app->reviewed_at,
                'reviewer_notes'  => $app->reviewer_notes,

                'week' => $app->week ? [
                    'id'           => $app->week->id,
                    'week_number'  => $app->week->week_number,
                    'year'         => $app->week->year,
                    'status'       => $app->week->status,
                    'voting_starts_at' => $app->week->voting_starts_at,
                    'voting_ends_at'   => $app->week->voting_ends_at,
                ] : null,

                'spotlightable' => $spotlight ? [
                    'id'   => $spotlight->id,
                    'type' => $isArtist ? 'artist' : 'business',
                    'name' => $isArtist
                        ? ($spotlight->artist_stage_name ?? $spotlight->full_legal_name)
                        : ($spotlight->business_name ?? $spotlight->owner_founder_name),
                    'city'  => $spotlight->city ?? null,
                    'state' => $spotlight->state ?? null,
                    'email' => $spotlight->email ?? null,
                    'status' => $spotlight->status ?? null,
                ] : null,
            ];
        });

        return $this->success('My applications retrieved.', [
            'applications' => $data,
            'pagination' => [
                'total'        => $applications->total(),
                'per_page'     => $applications->perPage(),
                'current_page' => $applications->currentPage(),
                'last_page'    => $applications->lastPage(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    private function resolveSpotlight(string $type, int $id)
    {
        return match ($type) {
            'artist'   => ArtistSpotlight::find($id),
            'business' => BusinessSpotlight::find($id),
            default    => null,
        };
    }

    private function getMorphType(string $type): string
    {
        return match ($type) {
            'artist'   => ArtistSpotlight::class,
            'business' => BusinessSpotlight::class,
            default    => ArtistSpotlight::class,
        };
    }

    private function userOwnsSpotlight($user, $spotlight, string $type): bool
    {
        if ($type === 'artist') {
            return $spotlight->user_id === $user->id;
        }

        if ($type === 'business') {
            return $spotlight->user_id === $user->id;
        }

        return false;
    }
}
