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

        $data['slug'] = $data['slug'] ?? Str::slug($data['business_name']);

        $business = Business::create([
            'user_id' => auth('api')->user()->id,
            'business_category_id' => $data['business_category_id'],
            'owner_name'           => $data['owner_name'],
            'business_name'        => $data['business_name'],
            'slug'                 => $data['slug'],
            'year_founded'         => $data['year_founded'],
            'website'              => $data['website'] ?? null,
            'city'                 => $data['city'],
            'state'                => $data['state'],
            'description'         => $data['description'] ?? null,
            'logo'                => $data['logo'] ?? null,
            'status'              => $data['status'] ?? 'active',
            'is_featured'         => $data['is_featured'] ?? false,
        ]);

        DB::commit();

        return $business->load(['user.profile', 'category']);
    }

    /**
     * Update an existing business.
     */
    public function update(Business $business, array $data): Business
    {
        DB::beginTransaction();

        $updateData = [];

        if (array_key_exists('business_category_id', $data)) {
            $updateData['business_category_id'] = $data['business_category_id'];
        }
        if (array_key_exists('owner_name', $data)) {
            $updateData['owner_name'] = $data['owner_name'];
        }
        if (array_key_exists('business_name', $data)) {
            $updateData['business_name'] = $data['business_name'];
            $updateData['slug'] = Str::slug($data['business_name']);
        }
        if (array_key_exists('slug', $data)) {
            $updateData['slug'] = Str::slug($data['slug']);
        }
        if (array_key_exists('year_founded', $data)) {
            $updateData['year_founded'] = $data['year_founded'];
        }
        if (array_key_exists('website', $data)) {
            $updateData['website'] = $data['website'];
        }
        if (array_key_exists('city', $data)) {
            $updateData['city'] = $data['city'];
        }
        if (array_key_exists('state', $data)) {
            $updateData['state'] = $data['state'];
        }
        if (array_key_exists('description', $data)) {
            $updateData['description'] = $data['description'];
        }
        if (array_key_exists('logo', $data)) {
            $updateData['logo'] = $data['logo'];
        }
        if (array_key_exists('status', $data)) {
            $updateData['status'] = $data['status'];
        }
        if (array_key_exists('is_featured', $data)) {
            $updateData['is_featured'] = filter_var($data['is_featured'], FILTER_VALIDATE_BOOLEAN);
        }

        $business->update($updateData);

        DB::commit();

        return $business->load(['user.profile', 'category']);
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
            'user_id'    => $userId,
            'business_id'=> $business->id,
            'action_type'=> 'clap',
            'ip'         => $ip,
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
            'user_id'    => $userId,
            'business_id'=> $business->id,
            'action_type'=> 'save',
            'ip'         => $ip,
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
            'user_id'    => $userId,
            'business_id'=> $business->id,
            'action_type'=> 'share',
            'ip'         => $ip,
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
        $interactions = BusinessInteraction::where('user_id', $userId)
            ->where('business_id', $business->id)
            ->pluck('action_type')
            ->toArray();

        return [
            'is_clapped' => in_array('clap', $interactions),
            'is_saved'   => in_array('save', $interactions),
            'is_shared'  => in_array('share', $interactions),
        ];
    }
}
