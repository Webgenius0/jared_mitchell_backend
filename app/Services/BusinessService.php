<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessInteraction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessService
{
    /**
     * List businesses with optional filters.
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Business::with(['user.profile', 'category']);

        // Search by business name or owner name
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by category
        if (!empty($filters['business_category_id'])) {
            $query->where('business_category_id', $filters['business_category_id']);
        }

        // Filter by featured
        if (isset($filters['is_featured'])) {
            $query->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN));
        }

        // Sort
        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Create a new business.
     */
    public function create(array $data): Business
    {
        DB::beginTransaction();

        $slug = Str::slug($data['business_name']);
        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (Business::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $photoVideoPath = null;
        if (request()->hasFile('photo_video')) {
            $photoVideoPath = request()->file('photo_video')->store('businesses/media', 'public');
        }

        $business = Business::create([
            'user_id' => auth('api')->id(),
            'business_name' => $data['business_name'],
            'slug' => $slug,
            'owner_founder_name' => $data['owner_founder_name'] ?? null,
            'story' => $data['story'] ?? null,
            'mission' => $data['mission'] ?? null,
            'website_social_media' => $data['website_social_media'] ?? null,
            'community_impact_statement' => $data['community_impact_statement'] ?? null,
            'revenue_stage' => $data['revenue_stage'] ?? null,
            'why_they_deserve_to_compete' => $data['why_they_deserve_to_compete'] ?? null,
            'photo_video' => $photoVideoPath,
            'status' => $data['status'] ?? 'active',
        ]);

        DB::commit();

        return $business->load(['user.profile']);
    }

    /**
     * Update an existing business.
     */
    public function update(Business $business, array $data): Business
    {
        DB::beginTransaction();

        $updateData = [];

        $fields = [
            'business_name',
            'owner_founder_name',
            'story',
            'mission',
            'website_social_media',
            'community_impact_statement',
            'revenue_stage',
            'why_they_deserve_to_compete'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (array_key_exists('business_name', $data) && $data['business_name'] !== $business->business_name) {
            $slug = Str::slug($data['business_name']);
            $originalSlug = $slug;
            $counter = 1;
            while (Business::where('slug', $slug)->where('id', '!=', $business->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $updateData['slug'] = $slug;
        }

        if (request()->hasFile('photo_video')) {
            if ($business->photo_video) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($business->photo_video);
            }
            $updateData['photo_video'] = request()->file('photo_video')->store('businesses/media', 'public');
        }

        $business->update($updateData);

        DB::commit();

        return $business->load(['user.profile']);
    }

    /**
     * Delete (soft delete) a business.
     */
    public function delete(Business $business): bool
    {
        DB::beginTransaction();
        $result = $business->delete();
        DB::commit();

        return $result;
    }

    /**
     * Toggle business status between active and inactive.
     */
    public function toggleStatus(Business $business): Business
    {
        $business->update([
            'status' => $business->status === 'active' ? 'inactive' : 'active',
        ]);

        return $business->load(['user.profile', 'category']);
    }

    /**
     * Terminate a business (set status to terminated).
     */
    public function terminate(Business $business): Business
    {
        $business->update([
            'status' => 'terminated',
        ]);

        return $business->load(['user.profile', 'category']);
    }

    /**
     * Toggle clap (like/unlike) for a business by a user.
     * Clap: +1 total_claps, +1 total_points
     * Unclap: -1 total_claps, -1 total_points
     */
    public function toggleClap(Business $business, int $userId, ?string $ip = null, ?string $userAgent = null): array
    {
        $existing = BusinessInteraction::where('user_id', $userId)
            ->where('business_id', $business->id)
            ->where('action_type', 'clap')
            ->first();

        if ($existing) {
            $existing->delete();
            $business->decrement('total_claps');
            $business->decrement('total_points', 1);

            return [
                'is_clapped' => false,
                'total_claps' => max(0, $business->fresh()->total_claps),
                'total_points' => max(0, $business->fresh()->total_points),
            ];
        }

        BusinessInteraction::create([
            'user_id' => $userId,
            'business_id' => $business->id,
            'action_type' => 'clap',
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);

        $business->increment('total_claps');
        $business->increment('total_points', 1);

        return [
            'is_clapped' => true,
            'total_claps' => $business->fresh()->total_claps,
            'total_points' => $business->fresh()->total_points,
        ];
    }

    /**
     * Toggle save/unsave for a business by a user.
     * Save: +1 total_saves, +3 total_points
     * Unsave: -1 total_saves, -3 total_points
     */
    public function toggleSave(Business $business, int $userId, ?string $ip = null, ?string $userAgent = null): array
    {
        $existing = BusinessInteraction::where('user_id', $userId)
            ->where('business_id', $business->id)
            ->where('action_type', 'save')
            ->first();

        if ($existing) {
            $existing->delete();
            $business->decrement('total_saves');
            $business->decrement('total_points', 3);

            return [
                'is_saved' => false,
                'total_saves' => max(0, $business->fresh()->total_saves),
                'total_points' => max(0, $business->fresh()->total_points),
            ];
        }

        BusinessInteraction::create([
            'user_id' => $userId,
            'business_id' => $business->id,
            'action_type' => 'save',
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);

        $business->increment('total_saves');
        $business->increment('total_points', 3);

        return [
            'is_saved' => true,
            'total_saves' => $business->fresh()->total_saves,
            'total_points' => $business->fresh()->total_points,
        ];
    }

    /**
     * Toggle share/unshare for a business by a user.
     * Share: +1 total_shares, +5 total_points
     * Unshare: -1 total_shares, -5 total_points
     */
    public function toggleShare(Business $business, int $userId, ?string $ip = null, ?string $userAgent = null): array
    {
        $existing = BusinessInteraction::where('user_id', $userId)
            ->where('business_id', $business->id)
            ->where('action_type', 'share')
            ->first();

        if ($existing) {
            $existing->delete();
            $business->decrement('total_shares');
            $business->decrement('total_points', 5);

            return [
                'is_shared' => false,
                'total_shares' => max(0, $business->fresh()->total_shares),
                'total_points' => max(0, $business->fresh()->total_points),
            ];
        }

        BusinessInteraction::create([
            'user_id' => $userId,
            'business_id' => $business->id,
            'action_type' => 'share',
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);

        $business->increment('total_shares');
        $business->increment('total_points', 5);

        return [
            'is_shared' => true,
            'total_shares' => $business->fresh()->total_shares,
            'total_points' => $business->fresh()->total_points,
        ];
    }

    /**
     * Get interaction state for the authenticated user.
     */
    public function getUserInteractionState(Business $business, int $userId): array
    {
        return [
            'is_clapped' => BusinessInteraction::where('business_id', $business->id)->where('user_id', $userId)->where('action_type', 'clap')->exists(),
            'is_saved' => BusinessInteraction::where('business_id', $business->id)->where('user_id', $userId)->where('action_type', 'save')->exists(),
            'is_shared' => BusinessInteraction::where('business_id', $business->id)->where('user_id', $userId)->where('action_type', 'share')->exists(),
        ];
    }
}
