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
            $query = Business::with(['user.profile', 'category'])->latest();

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by category
            if ($request->filled('business_category_id')) {
                $query->where('business_category_id', $request->business_category_id);
            }

            // Filter by featured
            if ($request->filled('is_featured')) {
                $query->where('is_featured', $request->is_featured === 'yes');
            }

            // Custom search
            if ($request->filled('search_term')) {
                $search = $request->search_term;
                $query->where(function ($q) use ($search) {
                    $q->where('business_name', 'like', "%{$search}%")
                        ->orWhere('owner_name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%");
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('owner', function (Business $business) {
                    $name = $business->user?->profile?->name ?? $business->user?->email ?? '—';
                    return '<div>
                        <strong>' . e($business->owner_name) . '</strong>
                        <br><small class="text-muted">' . e($name) . '</small>
                    </div>';
                })
                ->addColumn('business', function (Business $business) {
                    $category = $business->category?->name ?? '—';
                    return '<div>
                        <strong>' . e($business->business_name) . '</strong>
                        <br><small class="text-muted">' . e($category) . '</small>
                    </div>';
                })
                ->addColumn('location', function (Business $business) {
                    return e($business->city) . ', ' . e($business->state);
                })
                ->editColumn('status', function (Business $business) {
                    $badges = [
                        'active'     => 'bg-success-subtle text-success',
                        'inactive'   => 'bg-secondary-subtle text-secondary',
                        'terminated' => 'bg-danger-subtle text-danger',
                    ];
                    $class = $badges[$business->status] ?? 'bg-secondary-subtle text-secondary';
                    $label = ucfirst($business->status);
                    return '<span class="badge ' . $class . '">' . $label . '</span>';
                })
                ->addColumn('featured', function (Business $business) {
                    return $business->is_featured
                        ? '<span class="badge bg-warning-subtle text-warning"><i class="ri-star-fill me-1"></i>Featured</span>'
                        : '<span class="badge bg-light text-muted">—</span>';
                })
                ->addColumn('engagement', function (Business $business) {
                    return '<div class="text-nowrap">
                        <i class="ri-hand-heart-line text-muted me-1" title="Claps"></i>' . number_format($business->total_claps) . '
                        <i class="ri-bookmark-line text-muted ms-2 me-1" title="Saves"></i>' . number_format($business->total_saves) . '
                        <i class="ri-share-line text-muted ms-2 me-1" title="Shares"></i>' . number_format($business->total_shares) . '
                        <i class="ri-fire-line text-muted ms-2 me-1" title="Points"></i>' . number_format($business->total_points) . '
                    </div>';
                })
                ->addColumn('action', function (Business $business) {
                    $viewBtn = '<button class="btn btn-sm btn-soft-info view-btn" data-id="' . $business->id . '" title="View"><i class="ri-eye-line"></i></button>';

                    $statusIcon = $business->status === 'active' ? 'ri-pause-circle-line' : 'ri-play-circle-line';
                    $statusTitle = $business->status === 'active' ? 'Deactivate' : 'Activate';
                    $toggleBtn = '<button class="btn btn-sm btn-soft-warning toggle-status-btn" data-id="' . $business->id . '" data-status="' . $business->status . '" title="' . $statusTitle . '"><i class="' . $statusIcon . '"></i></button>';

                    $deleteBtn = '<button class="btn btn-sm btn-soft-danger delete-btn" data-id="' . $business->id . '" title="Delete"><i class="ri-delete-bin-line"></i></button>';

                    return '<div class="d-flex gap-1 justify-content-center">' . $viewBtn . $toggleBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['owner', 'business', 'status', 'featured', 'engagement', 'action'])
                ->make(true);
        }

        $categories = BusinessCategory::orderBy('name')->get(['id', 'name']);
        $stats = [
            'total'       => Business::count(),
            'active'      => Business::where('status', 'active')->count(),
            'inactive'    => Business::where('status', 'inactive')->count(),
            'terminated'  => Business::where('status', 'terminated')->count(),
            'featured'    => Business::where('is_featured', true)->count(),
        ];

        return view('web.admin.businesses.index', compact('categories', 'stats'));
    }

    /**
     * Get a single business for the details modal.
     */
    public function show(Business $business)
    {
        $business->load(['user.profile', 'category']);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                   => $business->id,
                'user_id'              => $business->user_id,
                'business_category_id' => $business->business_category_id,
                'owner_name'           => $business->owner_name,
                'business_name'        => $business->business_name,
                'slug'                 => $business->slug,
                'year_founded'         => $business->year_founded,
                'website'              => $business->website,
                'city'                 => $business->city,
                'state'                => $business->state,
                'description'          => $business->description,
                'logo'                 => $business->logo ? asset('storage/' . $business->logo) : null,
                'status'               => $business->status,
                'is_featured'          => (bool) $business->is_featured,
                'category_name'        => $business->category?->name,
                'user_name'            => $business->user?->profile?->name ?? $business->user?->email ?? '—',
                'user_email'           => $business->user?->email ?? '—',
                'total_claps'          => (int) $business->total_claps,
                'total_saves'          => (int) $business->total_saves,
                'total_shares'         => (int) $business->total_shares,
                'total_points'         => (int) $business->total_points,
                'created_at'           => $business->created_at?->format('M d, Y h:i A'),
                'updated_at'           => $business->updated_at?->format('M d, Y h:i A'),
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
                'data'    => ['status' => $newStatus],
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
