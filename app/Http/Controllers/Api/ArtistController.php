<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArtistResource;
use App\Models\ArtistSpotlight;
use App\Models\EventRegistration;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/artists
     *
     * List all artists with search and category filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::role('artist', 'api')
            ->with(['profile', 'artistCategory'])
            ->withCount(['likers', 'bookmarkers', 'shares'])
            ->where('status', 'active');

        // Search by name or username
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('profile', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('artist_category_id', $request->category_id);
        }

        // Ordering (Descending by default as requested)
        $artists = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 12));

        return $this->success(
            'Artists retrieved successfully.',
            [
                'artists' => ArtistResource::collection($artists),
                'pagination' => [
                    'current_page' => $artists->currentPage(),
                    'per_page' => $artists->perPage(),
                    'total' => $artists->total(),
                    'last_page' => $artists->lastPage(),
                ]
            ]
        );
    }

    /**
     * GET /api/v1/artists/{id}
     *
     * Get detailed profile of an artist.
     */
    public function show($id): JsonResponse
    {
        $artist = User::role('artist', 'api')
            ->with(['profile', 'artistCategory'])
            ->withCount(['likers', 'bookmarkers', 'shares'])
            ->where('status', 'active')
            ->find($id);

        if (!$artist) {
            return $this->error('Artist not found.', null, 404);
        }

        return $this->success(
            'Artist profile retrieved successfully.',
            new ArtistResource($artist)
        );
    }

    /**
     * GET /api/v1/artists/{id}/analytics
     *
     * Get artist analytics for a given artist including:
     * - Basic artist info
     * - Total spotlight count & total approved spotlight count
     * - Total event ticket purchases
     * - Monthly performance chart (12 months, normalized 0-100)
     */
    public function analytics($id): JsonResponse
    {
        $artist = User::role('artist', 'api')
            ->with(['profile', 'artistCategory'])
            ->where('status', 'active')
            ->find($id);

        if (!$artist) {
            return $this->error('Artist not found.', null, 404);
        }

        // --- Statistics ---

        // Total spotlight records
        $totalSpotlights = ArtistSpotlight::where('user_id', $artist->id)->count();

        // Total approved/selected spotlight records
        $totalApprovedSpotlights = ArtistSpotlight::where('user_id', $artist->id)
            ->whereIn('status', ['approved', 'selected'])
            ->count();

        // Total ticket purchases (paid event registrations)
        $totalTicketPurchases = EventRegistration::where('user_id', $artist->id)
            ->where('payment_status', 'paid')
            ->count();

        // --- Monthly Performance (current year, normalized 0-100) ---

        $currentYear = now()->year;
        $monthlyPerformance = [];

        $artistSpotlightIds = ArtistSpotlight::where('user_id', $artist->id)->pluck('id');

        if ($artistSpotlightIds->isNotEmpty()) {
            // Get all nominee entries for this artist's spotlights within the current year
            $nominees = SpotlightWeekNominee::where('spotlightable_type', ArtistSpotlight::class)
                ->whereIn('spotlightable_id', $artistSpotlightIds)
                ->whereHas('week', function ($q) use ($currentYear) {
                    $q->whereYear('voting_starts_at', $currentYear)
                      ->orWhereYear('voting_ends_at', $currentYear);
                })
                ->with('week')
                ->get();

            // Aggregate total votes per month
            $monthlyVotes = [];
            foreach ($nominees as $nominee) {
                if ($nominee->week && $nominee->week->voting_starts_at) {
                    $month = $nominee->week->voting_starts_at->month;
                    $monthlyVotes[$month] = ($monthlyVotes[$month] ?? 0) + $nominee->total_vote_count;
                }
            }

            $maxVotes = !empty($monthlyVotes) ? max($monthlyVotes) : 1;

            for ($m = 1; $m <= 12; $m++) {
                $votes = $monthlyVotes[$m] ?? 0;
                $value = $maxVotes > 0 ? (int) round(($votes / $maxVotes) * 100) : 0;
                $monthlyPerformance[] = [
                    'month' => $m,
                    'label' => date('M', mktime(0, 0, 0, $m, 1)),
                    'value' => min($value, 100),
                ];
            }
        } else {
            // No spotlight data — return zero-fill for all 12 months
            for ($m = 1; $m <= 12; $m++) {
                $monthlyPerformance[] = [
                    'month' => $m,
                    'label' => date('M', mktime(0, 0, 0, $m, 1)),
                    'value' => 0,
                ];
            }
        }

        return $this->success('Artist analytics retrieved successfully.', [
            'artist' => [
                'id'       => $artist->id,
                'name'     => $artist->profile->name ?? '',
                'username' => $artist->profile->username ?? '',
                'avatar'   => $artist->profile->avatar_url ?? asset('admin/default/user.jpg'),
                'category' => [
                    'id'   => $artist->artistCategory->id ?? null,
                    'name' => $artist->artistCategory->name ?? 'Uncategorized',
                    'slug' => $artist->artistCategory->slug ?? '',
                ],
            ],
            'statistics' => [
                'total_spotlights'         => $totalSpotlights,
                'total_approved_spotlights' => $totalApprovedSpotlights,
                'total_ticket_purchases'   => $totalTicketPurchases,
            ],
            'performance' => [
                'year'         => $currentYear,
                'max_value'    => 100,
                'monthly_data' => $monthlyPerformance,
            ],
        ]);
    }

    /**
     * POST /api/v1/artists/{id}/like
     */
    public function toggleLike($id): JsonResponse
    {
        $user = auth()->user();
        if ($user->id == $id) {
            return $this->error('You cannot like your own profile.', null, 403);
        }

        $artist = User::role('artist', 'api')->findOrFail($id);
        $exists = $user->likedArtists()->where('artist_id', $id)->exists();

        if ($exists) {
            $user->likedArtists()->detach($id);
            $message = 'Artist unliked successfully.';
            $liked = false;
        } else {
            $user->likedArtists()->attach($id);
            $message = 'Artist liked successfully.';
            $liked = true;
        }

        return $this->success($message, ['is_liked' => $liked]);
    }

    /**
     * POST /api/v1/artists/{id}/bookmark
     */
    public function toggleBookmark($id): JsonResponse
    {
        $user = auth()->user();
        if ($user->id == $id) {
            return $this->error('You cannot bookmark your own profile.', null, 403);
        }

        $artist = User::role('artist', 'api')->findOrFail($id);
        $exists = $user->bookmarkedArtists()->where('artist_id', $id)->exists();

        if ($exists) {
            $user->bookmarkedArtists()->detach($id);
            $message = 'Artist removed from bookmarks.';
            $bookmarked = false;
        } else {
            $user->bookmarkedArtists()->attach($id);
            $message = 'Artist bookmarked successfully.';
            $bookmarked = true;
        }

        return $this->success($message, ['is_bookmarked' => $bookmarked]);
    }

    /**
     * POST /api/v1/artists/{id}/share
     */
    public function recordShare(Request $request, $id): JsonResponse
    {
        $user = auth()->user();
        if ($user && $user->id == $id) {
            return $this->error('You cannot share your own profile.', null, 403);
        }

        $artist = User::role('artist', 'api')->findOrFail($id);

        $artist->shares()->create([
            'user_id' => $user?->id,
            'platform' => $request->platform,
        ]);

        return $this->success('Artist share recorded successfully.');
    }
}
