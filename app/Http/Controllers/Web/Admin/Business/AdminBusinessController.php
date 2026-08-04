<?php

namespace App\Http\Controllers\Web\Admin\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class AdminBusinessController extends Controller
{
    /**
     * Display a listing of businesses.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Business::with(['user.profile'])->latest();

            // Status Filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search
            if ($request->filled('search_term')) {
                $search = $request->search_term;

                $query->where(function ($q) use ($search) {
                    $q->where('business_name', 'like', "%{$search}%")
                        ->orWhere('owner_founder_name', 'like', "%{$search}%")
                        ->orWhere('story', 'like', "%{$search}%")
                        ->orWhere('mission', 'like', "%{$search}%")
                        ->orWhere('website_social_media', 'like', "%{$search}%")
                        ->orWhere('community_impact_statement', 'like', "%{$search}%")
                        ->orWhere('revenue_stage', 'like', "%{$search}%")
                        ->orWhere('why_they_deserve_to_compete', 'like', "%{$search}%");
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('business', function (Business $business) {
                    return '
                    <div>
                        <strong>' . e($business->business_name) . '</strong>
                        <br>
                        <small class="text-muted">' . e($business->slug) . '</small>
                    </div>
                ';
                })

                ->addColumn('owner', function (Business $business) {
                    return e($business->owner_founder_name ?? '—');
                })

                ->addColumn('story', function (Business $business) {
                    return \Illuminate\Support\Str::limit(strip_tags($business->story), 60);
                })

                ->addColumn('mission', function (Business $business) {
                    return \Illuminate\Support\Str::limit(strip_tags($business->mission), 60);
                })

                ->addColumn('website', function (Business $business) {
                    if (!$business->website_social_media) {
                        return '—';
                    }

                    return '<a href="' . e($business->website_social_media) . '" target="_blank">
                            Visit
                        </a>';
                })

                ->addColumn('revenue_stage', function (Business $business) {
                    return e($business->revenue_stage ?? '—');
                })

                ->addColumn('media', function (Business $business) {

                    if (!$business->photo_video) {
                        return '—';
                    }

                    $url = asset('storage/' . $business->photo_video);

                    $extension = strtolower(pathinfo($business->photo_video, PATHINFO_EXTENSION));

                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                        return '<img src="' . $url . '" width="60" class="rounded border">';
                    }

                    return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-primary">
                            View Video
                        </a>';
                })

                ->editColumn('status', function (Business $business) {

                    $badges = [
                        'active' => 'bg-success-subtle text-success',
                        'inactive' => 'bg-secondary-subtle text-secondary',
                        'terminated' => 'bg-danger-subtle text-danger',
                    ];

                    $class = $badges[$business->status] ?? 'bg-secondary-subtle text-secondary';

                    return '<span class="badge ' . $class . '">' . ucfirst($business->status) . '</span>';
                })

                ->addColumn('created_at', function (Business $business) {
                    return $business->created_at->format('d M Y');
                })

                ->addColumn('action', function (Business $business) {

                    $viewBtn = '
                    <button
                        class="btn btn-sm btn-soft-info view-btn"
                        data-id="' . $business->id . '"
                        title="View">
                        <i class="ri-eye-line"></i>
                    </button>
                ';

                    $statusIcon = $business->status == 'active'
                        ? 'ri-pause-circle-line'
                        : 'ri-play-circle-line';

                    $statusTitle = $business->status == 'active'
                        ? 'Deactivate'
                        : 'Activate';

                    $toggleBtn = '
                    <button
                        class="btn btn-sm btn-soft-warning toggle-status-btn"
                        data-id="' . $business->id . '"
                        data-status="' . $business->status . '"
                        title="' . $statusTitle . '">
                        <i class="' . $statusIcon . '"></i>
                    </button>
                ';

                    $deleteBtn = '
                    <button
                        class="btn btn-sm btn-soft-danger delete-btn"
                        data-id="' . $business->id . '"
                        title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                ';

                    return '
                    <div class="d-flex gap-1 justify-content-center">
                        ' . $viewBtn . '
                        ' . $toggleBtn . '
                        ' . $deleteBtn . '
                    </div>
                ';
                })

                ->rawColumns([
                    'business',
                    'website',
                    'media',
                    'status',
                    'action',
                ])

                ->make(true);
        }

        $stats = [
            'total' => Business::count(),
            'active' => Business::where('status', 'active')->count(),
            'inactive' => Business::where('status', 'inactive')->count(),
            'terminated' => Business::where('status', 'terminated')->count(),
        ];

        return view('web.admin.businesses.index', compact('stats'));
    }

    /**
     * Get a single business for the details modal.
     *
     * Returns the complete business payload: every business field, the owner
     * profile, and all business media (every picture/video) so the modal can
     * display everything.
     */
    public function show(Business $business)
    {
        $business->load(['user.profile', 'media', 'category']);

        $profile = $business->user?->profile;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $business->id,
                'user_id' => $business->user_id,

                'business_name' => $business->business_name,
                'slug' => $business->slug,
                'owner_founder_name' => $business->owner_founder_name,
                'owner_name' => $business->owner_name,

                'category_name' => $business->category?->name,

                'story' => $business->story,
                'mission' => $business->mission,
                'website_social_media' => $business->website_social_media,
                'community_impact_statement' => $business->community_impact_statement,
                'revenue_stage' => $business->revenue_stage,
                'why_they_deserve_to_compete' => $business->why_they_deserve_to_compete,

                'photo_video' => $business->photo_video
                    ? asset('storage/' . $business->photo_video)
                    : null,

                'status' => $business->status,
                'is_featured' => (bool) $business->is_featured,
                'total_claps' => (int) ($business->total_claps ?? 0),
                'total_saves' => (int) ($business->total_saves ?? 0),
                'total_shares' => (int) ($business->total_shares ?? 0),
                'total_points' => (int) ($business->total_points ?? 0),

                // Media gallery (every picture/video)
                'media' => $business->media->map(function ($m) {
                    return [
                        'id'        => $m->id,
                        'url'       => $m->file_path ? asset('storage/' . $m->file_path) : null,
                        'file_name' => $m->file_name,
                        'mime_type' => $m->mime_type,
                        'file_size' => $m->file_size,
                    ];
                })->values()->all(),

                // Owner / account
                'user_name' => $profile?->name
                    ?? $business->user?->email
                    ?? '—',

                'user_email' => $business->user?->email ?? '—',
                'user_username' => $profile?->username,
                'user_avatar' => $profile?->avatar
                    ? asset('storage/' . $profile->avatar)
                    : null,
                'user_biography' => $profile?->biography,
                'user_address' => $profile?->address,
                'user_website' => $profile?->website_link,
                'user_social_links' => $profile?->social_links,

                'created_at' => $business->created_at?->format('M d, Y h:i A'),
                'updated_at' => $business->updated_at?->format('M d, Y h:i A'),
            ],
        ]);
    }

    /**
     * Toggle business status between active and inactive.
     */
    public function toggleStatus(Business $business)
    {
        if ($business->status === 'terminated') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot toggle status of a terminated business.',
            ], 422);
        }

        try {
            $newStatus = $business->status === 'active' ? 'inactive' : 'active';
            $business->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => "Business status changed to {$newStatus} successfully.",
                'data' => ['status' => $newStatus],
            ]);
        } catch (Exception $e) {
            Log::error('Business status toggle failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update business status. Please try again.',
            ], 500);
        }
    }

    /**
     * Soft delete a business.
     */
    public function destroy(Business $business)
    {
        try {
            DB::beginTransaction();

            // Delete associated interactions
            $business->interactions()->delete();
            $business->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Business deleted successfully.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Business deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete business. Please try again.',
            ], 500);
        }
    }
}
