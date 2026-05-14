<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArtistResource;
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
