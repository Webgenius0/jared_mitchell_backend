<?php

namespace App\Http\Controllers\Web\Admin\BusinessSpotlight;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessSpotlightResource;
use App\Models\BusinessSpotlight;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AdminBusinessSpotlightController extends Controller
{
    use ApiResponse;

    /**
     * Display the business spotlights listing page.
     */
    public function index()
    {
        $stats = [
            'total' => BusinessSpotlight::count(),
            'by_status' => [
                'draft' => BusinessSpotlight::where('status', 'draft')->count(),
                'submitted' => BusinessSpotlight::where('status', 'submitted')->count(),
                'under_review' => BusinessSpotlight::where('status', 'under_review')->count(),
                'approved' => BusinessSpotlight::where('status', 'approved')->count(),
                'rejected' => BusinessSpotlight::where('status', 'rejected')->count(),
            ],
            'pending_review' => BusinessSpotlight::whereIn('status', ['submitted', 'under_review'])->count(),
        ];

        return view('web.admin.spotlight.index', compact('stats'));
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = BusinessSpotlight::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by service type
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        // Custom search
        if ($request->filled('search_term')) {
            $search = $request->search_term;
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('owner_founder_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('business', function ($row) {
                return '<div>
                    <strong>' . e($row->business_name) . '</strong>
                    <br><small class="text-muted">' . e($row->business_category) . '</small>
                </div>';
            })
            ->addColumn('owner', function ($row) {
                return '<div>
                    <span>' . e($row->owner_founder_name) . '</span>
                    <br><small class="text-muted">' . e($row->email) . '</small>
                </div>';
            })
            ->addColumn('location', function ($row) {
                return e($row->city) . ', ' . e($row->state);
            })
            ->addColumn('service_type', function ($row) {
                $labels = [
                    'in_person_only' => '<span class="badge bg-primary-subtle text-primary">In-Person</span>',
                    'online_only' => '<span class="badge bg-info-subtle text-info">Online</span>',
                    'both_in_person_and_online' => '<span class="badge bg-success-subtle text-success">Both</span>',
                ];
                return $labels[$row->service_type] ?? '-';
            })
            ->addColumn('status', function ($row) {
                $badges = [
                    'draft' => 'bg-secondary-subtle text-secondary',
                    'submitted' => 'bg-primary-subtle text-primary',
                    'under_review' => 'bg-warning-subtle text-warning',
                    'approved' => 'bg-success-subtle text-success',
                    'rejected' => 'bg-danger-subtle text-danger',
                ];
                $class = $badges[$row->status] ?? 'bg-secondary';
                $label = ucwords(str_replace('_', ' ', $row->status));
                return '<span class="badge ' . $class . '">' . $label . '</span>';
            })
            ->addColumn('submitted_at', function ($row) {
                return $row->submitted_at ? $row->submitted_at->format('M d, Y H:i') : '-';
            })
            ->addColumn('action', function ($row) {
                $viewBtn = '<button class="btn btn-sm btn-soft-info view-btn" data-id="' . $row->id . '" title="View"><i class="ri-eye-line"></i></button>';
                
                $approveBtn = '';
                $rejectBtn = '';
                $deleteBtn = '<button class="btn btn-sm btn-soft-danger delete-btn" data-id="' . $row->id . '" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                
                if ($row->status !== 'approved' && $row->status !== 'draft') {
                    $approveBtn = '<button class="btn btn-sm btn-soft-success approve-btn" data-id="' . $row->id . '" title="Approve"><i class="ri-checkbox-circle-line"></i></button>';
                }
                
                if ($row->status !== 'rejected' && $row->status !== 'draft') {
                    $rejectBtn = '<button class="btn btn-sm btn-soft-warning reject-btn" data-id="' . $row->id . '" title="Reject"><i class="ri-close-circle-line"></i></button>';
                }
                
                return '<div class="d-flex gap-1 justify-content-center">' . $viewBtn . $approveBtn . $rejectBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['business', 'owner', 'service_type', 'status', 'action'])
            ->make(true);
    }

    /**
     * Get a single business spotlight by ID.
     */
    public function show(int $id)
    {
        $spotlight = BusinessSpotlight::with('reviewer')->find($id);

        if (!$spotlight) {
            return $this->notFound('Business spotlight not found.');
        }

        return $this->success(
            'Business spotlight retrieved successfully.',
            new BusinessSpotlightResource($spotlight)
        );
    }

    /**
     * Update the status of a business spotlight (approve/reject/under_review).
     */
    public function updateStatus(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:under_review,approved,rejected',
            'reviewer_notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $spotlight = BusinessSpotlight::find($id);

        if (!$spotlight) {
            return $this->notFound('Business spotlight not found.');
        }

        // Prevent updating draft submissions directly
        if ($spotlight->status === 'draft') {
            return $this->error(
                null,
                'Cannot update status of a draft submission. Wait for user to submit.',
                422
            );
        }

        try {
            DB::beginTransaction();

            $spotlight->update([
                'status' => $request->status,
                'reviewed_by' => Auth::id(),
                'reviewer_notes' => $request->reviewer_notes,
            ]);

            DB::commit();

            return $this->success(
                "Business spotlight {$request->status} successfully.",
                new BusinessSpotlightResource($spotlight->fresh('reviewer'))
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Business spotlight status update failed: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update status. Please try again.',
                500
            );
        }
    }

    /**
     * Approve a business spotlight.
     */
    public function approve(Request $request, int $id)
    {
        $request->merge(['status' => 'approved']);
        return $this->updateStatus($request, $id);
    }

    /**
     * Reject a business spotlight.
     */
    public function reject(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'reviewer_notes' => 'required|string|max:2000',
        ], [
            'reviewer_notes.required' => 'Please provide a reason for rejection.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $request->merge(['status' => 'rejected']);
        return $this->updateStatus($request, $id);
    }

    /**
     * Mark a business spotlight as under review.
     */
    public function markUnderReview(Request $request, int $id)
    {
        $request->merge(['status' => 'under_review']);
        return $this->updateStatus($request, $id);
    }

    /**
     * Soft delete a business spotlight.
     */
    public function destroy(int $id)
    {
        $spotlight = BusinessSpotlight::find($id);

        if (!$spotlight) {
            return $this->notFound('Business spotlight not found.');
        }

        try {
            $spotlight->delete();

            return $this->success('Business spotlight deleted successfully.');
        } catch (Exception $e) {
            Log::error('Business spotlight deletion failed: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to delete business spotlight. Please try again.',
                500
            );
        }
    }

    /**
     * Restore a soft-deleted business spotlight.
     */
    public function restore(int $id)
    {
        $spotlight = BusinessSpotlight::withTrashed()->find($id);

        if (!$spotlight) {
            return $this->notFound('Business spotlight not found.');
        }

        if (!$spotlight->trashed()) {
            return $this->error(null, 'Business spotlight is not deleted.', 422);
        }

        try {
            $spotlight->restore();

            return $this->success(
                'Business spotlight restored successfully.',
                new BusinessSpotlightResource($spotlight)
            );
        } catch (Exception $e) {
            Log::error('Business spotlight restoration failed: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to restore business spotlight. Please try again.',
                500
            );
        }
    }

    /**
     * Get statistics/summary of business spotlights.
     */
    public function statistics()
    {
        $stats = [
            'total' => BusinessSpotlight::count(),
            'by_status' => [
                'draft' => BusinessSpotlight::where('status', 'draft')->count(),
                'submitted' => BusinessSpotlight::where('status', 'submitted')->count(),
                'under_review' => BusinessSpotlight::where('status', 'under_review')->count(),
                'approved' => BusinessSpotlight::where('status', 'approved')->count(),
                'rejected' => BusinessSpotlight::where('status', 'rejected')->count(),
            ],
            'by_service_type' => [
                'in_person_only' => BusinessSpotlight::where('service_type', 'in_person_only')->count(),
                'online_only' => BusinessSpotlight::where('service_type', 'online_only')->count(),
                'both' => BusinessSpotlight::where('service_type', 'both_in_person_and_online')->count(),
            ],
            'this_month' => BusinessSpotlight::whereMonth('submitted_at', now()->month)
                ->whereYear('submitted_at', now()->year)
                ->count(),
            'pending_review' => BusinessSpotlight::whereIn('status', ['submitted', 'under_review'])->count(),
        ];

        return $this->success('Statistics retrieved successfully.', $stats);
    }

    /**
     * Bulk update status for multiple spotlights.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:business_spotlights,id',
            'status' => 'required|in:under_review,approved,rejected',
            'reviewer_notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // Require notes for bulk rejection
        if ($request->status === 'rejected' && empty($request->reviewer_notes)) {
            return $this->validationError([
                'reviewer_notes' => ['Please provide a reason for rejection.']
            ]);
        }

        try {
            DB::beginTransaction();

            $updated = BusinessSpotlight::whereIn('id', $request->ids)
                ->where('status', '!=', 'draft')
                ->update([
                    'status' => $request->status,
                    'reviewed_by' => Auth::id(),
                    'reviewer_notes' => $request->reviewer_notes,
                ]);

            DB::commit();

            return $this->success(
                "{$updated} business spotlight(s) updated to {$request->status}.",
                ['updated_count' => $updated]
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Bulk status update failed: ' . $e->getMessage());

            return $this->error(
                ['exception' => $e->getMessage()],
                'Failed to update statuses. Please try again.',
                500
            );
        }
    }
}
