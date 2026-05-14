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
        $query = User::role('artist')
            ->with(['profile', 'artistCategory'])
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
            ArtistResource::collection($artists)->response()->getData(true)
        );
    }

    /**
     * GET /api/v1/artists/{id}
     *
     * Get detailed profile of an artist.
     */
    public function show($id): JsonResponse
    {
        $artist = User::role('artist')
            ->with(['profile', 'artistCategory'])
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
}
