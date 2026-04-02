<?php

namespace App\Http\Controllers\Web\Admin\ArtistSpotlight;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArtistSpotlightResource;
use App\Models\ArtistSpotlight;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AdminArtistSpotlightController extends Controller
{
    use ApiResponse;

    /**
     * Display the artist spotlights listing page.
     */
    public function index()
    {
        $stats = [
            'total' => ArtistSpotlight::count(),
            'by_status' => [
                'draft' => ArtistSpotlight::where('status', 'draft')->count(),
                'submitted' => ArtistSpotlight::where('status', 'submitted')->count(),
                'under_review' => ArtistSpotlight::where('status', 'under_review')->count(),
                'approved' => ArtistSpotlight::where('status', 'approved')->count(),
                'rejected' => ArtistSpotlight::where('status', 'rejected')->count(),
            ],
            'pending_review' => ArtistSpotlight::whereIn('status', ['submitted', 'under_review'])->count(),
        ];

        return view('web.admin.spotlight.artists', compact('stats'));
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = ArtistSpotlight::with('category')->select('artist_spotlights.*');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('artist_category_id')) {
            $query->where('artist_category_id', $request->artist_category_id);
        }

        // Search
        if ($request->filled('search_term')) {
            $search = '%' . $request->search_term . '%';
            $query->where(function($q) use ($search) {
                $q->where('full_legal_name', 'like', $search)
                  ->orWhere('artist_stage_name', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('artist', function($row) {
                return '<div>
                    <strong>' . e($row->artist_stage_name) . '</strong><br>
                    <small class="text-muted">' . e($row->full_legal_name) . '</small>
                </div>';
            })
            ->addColumn('category', function($row) {
                return $row->category ? e($row->category->name) : '<span class="text-muted">Other</span>';
            })
            ->addColumn('location', function($row) {
                return e($row->city) . ', ' . e($row->state);
            })
            ->addColumn('status', function($row) {
                $badges = [
                    'draft' => 'bg-secondary-subtle text-secondary',
                    'submitted' => 'bg-primary-subtle text-primary',
                    'under_review' => 'bg-warning-subtle text-warning',
                    'approved' => 'bg-success-subtle text-success',
                    'rejected' => 'bg-danger-subtle text-danger',
                    'featured' => 'bg-info-subtle text-info',
                ];
                $class = $badges[$row->status] ?? 'bg-secondary';
                $label = ucwords(str_replace('_', ' ', $row->status));
                return '<span class="badge ' . $class . '">' . $label . '</span>';
            })
            ->addColumn('submitted_at', function($row) {
                return $row->submitted_at ? $row->submitted_at->format('M d, Y H:i') : '-';
            })
            ->addColumn('action', function($row) {
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
            ->rawColumns(['artist', 'status', 'action'])
            ->make(true);
    }

    /**
     * Show a single artist spotlight.
     */
    public function show(int $id)
    {
        $spotlight = ArtistSpotlight::with(['category', 'reviewer'])->find($id);

        if (!$spotlight) {
            return $this->notFound('Artist spotlight not found.');
        }

        return $this->success(
            'Artist spotlight retrieved successfully.',
            new ArtistSpotlightResource($spotlight)
        );
    }

    /**
     * Update the status of an artist spotlight.
     */
    public function updateStatus(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:under_review,approved,rejected,featured',
            'reviewer_notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $spotlight = ArtistSpotlight::find($id);

        if (!$spotlight) {
            return $this->notFound('Artist spotlight not found.');
        }

        if ($spotlight->status === 'draft') {
            return $this->error(null, 'Cannot update status of a draft submission.', 422);
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
                "Artist spotlight {$request->status} successfully.",
                new ArtistSpotlightResource($spotlight->fresh(['category', 'reviewer']))
            );
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Artist spotlight status update failed: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to update status.', 500);
        }
    }

    /**
     * Approve artist spotlight.
     */
    public function approve(Request $request, int $id)
    {
        $request->merge(['status' => 'approved']);
        return $this->updateStatus($request, $id);
    }

    /**
     * Reject artist spotlight.
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
     * Get statistics summary.
     */
    public function statistics()
    {
        $stats = [
            'total' => ArtistSpotlight::count(),
            'by_status' => [
                'draft' => ArtistSpotlight::where('status', 'draft')->count(),
                'submitted' => ArtistSpotlight::where('status', 'submitted')->count(),
                'under_review' => ArtistSpotlight::where('status', 'under_review')->count(),
                'approved' => ArtistSpotlight::where('status', 'approved')->count(),
                'rejected' => ArtistSpotlight::where('status', 'rejected')->count(),
            ],
            'this_month' => ArtistSpotlight::whereMonth('submitted_at', now()->month)->whereYear('submitted_at', now()->year)->count(),
            'pending_review' => ArtistSpotlight::whereIn('status', ['submitted', 'under_review'])->count(),
        ];

        return $this->success('Statistics retrieved successfully.', $stats);
    }

    /**
     * Bulk update status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:artist_spotlights,id',
            'status' => 'required|in:under_review,approved,rejected',
            'reviewer_notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            DB::beginTransaction();

            $updatedCount = ArtistSpotlight::whereIn('id', $request->ids)
                ->where('status', '!=', 'draft')
                ->update([
                    'status' => $request->status,
                    'reviewed_by' => Auth::id(),
                    'reviewer_notes' => $request->reviewer_notes,
                ]);

            DB::commit();

            return $this->success("{$updatedCount} artist spotlight(s) updated to {$request->status}.", ['updated_count' => $updatedCount]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Bulk status update failed: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to update statuses.', 500);
        }
    }

    /**
     * Soft delete artist spotlight.
     */
    public function destroy(int $id)
    {
        $spotlight = ArtistSpotlight::find($id);

        if (!$spotlight) {
            return $this->notFound('Artist spotlight not found.');
        }

        try {
            $spotlight->delete();
            return $this->success('Artist spotlight deleted successfully.');
        } catch (Exception $e) {
            Log::error('Artist spotlight deletion failed: ' . $e->getMessage());
            return $this->error(['exception' => $e->getMessage()], 'Failed to delete spotlight.', 500);
        }
    }
}
