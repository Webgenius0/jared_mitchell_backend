<?php

namespace App\Http\Controllers\Web\Admin\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\Spotlight\SpotlightApplication;
use App\Models\Spotlight\SpotlightWeekNominee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SpotlightApplicationController extends Controller
{
    /**
     * Display a listing of all spotlight applications across all weeks.
     */
    public function index(Request $request)
    {
        $stats = [
            'total'     => SpotlightApplication::count(),
            'pending'   => SpotlightApplication::where('status', 'pending')->count(),
            'selected'  => SpotlightApplication::where('status', 'selected')->count(),
            'rejected'  => SpotlightApplication::where('status', 'rejected')->count(),
            'withdrawn' => SpotlightApplication::where('status', 'withdrawn')->count(),
        ];

        if ($request->ajax()) {
            $query = SpotlightApplication::with([
                'week',
                'user.profile',
                'spotlightable',
            ])->latest('applied_at');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $type = $request->type === 'artist'
                    ? 'App\Models\ArtistSpotlight'
                    : 'App\Models\BusinessSpotlight';
                $query->where('spotlightable_type', $type);
            }

            if ($request->filled('search_query')) {
                $search = $request->search_query;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user.profile', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%");
                    })->orWhereHas('user', function ($u) use ($search) {
                        $u->where('email', 'like', "%{$search}%");
                    });
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('week_label', function ($row) {
                    if (!$row->week) return '—';
                    return 'Week ' . e($row->week->week_number) . ' (' . e($row->week->year) . ')';
                })
                ->addColumn('applicant', function ($row) {
                    $avatar = $row->user?->profile?->avatar_url
                        ?? asset('admin/default/user.jpg');

                    // Resolve name: profile name → spotlight name → email
                    $spotlightable = $row->spotlightable;
                    $spotlightName = $spotlightable?->business_name
                        ?? $spotlightable?->brand_name
                        ?? $spotlightable?->artist_stage_name
                        ?? $spotlightable?->full_legal_name
                        ?? null;
                    $name = $row->user?->profile?->name
                        ?? $spotlightName
                        ?? $row->user?->email
                        ?? '—';

                    $email = $row->user?->email ?? '';
                    return '
                        <div class="d-flex align-items-center gap-2">
                            <img src="' . e($avatar) . '" alt="" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                            <div>
                                <strong>' . e($name) . '</strong>
                                <br><small class="text-muted">' . e($email) . '</small>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('spotlight_name', function ($row) {
                    $spotlightable = $row->spotlightable;
                    if (!$spotlightable) return '—';
                    $name = $spotlightable->business_name
                        ?? $spotlightable->brand_name
                        ?? $spotlightable->artist_stage_name
                        ?? '#' . $spotlightable->id;
                    return e($name);
                })
                ->addColumn('spotlight_type', function ($row) {
                    $isArtist = $row->spotlightable_type === 'App\Models\ArtistSpotlight';
                    $icon = $isArtist ? 'ri-user-star-line' : 'ri-store-2-line';
                    $label = $isArtist ? 'Artist' : 'Business';
                    return '<span class="badge bg-light text-dark"><i class="' . $icon . ' me-1"></i>' . $label . '</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    $map = [
                        'pending'   => 'bg-warning-subtle text-warning',
                        'selected'  => 'bg-success-subtle text-success',
                        'rejected'  => 'bg-danger-subtle text-danger',
                        'withdrawn' => 'bg-secondary-subtle text-secondary',
                    ];
                    $class = $map[$row->status] ?? 'bg-secondary-subtle text-secondary';
                    return '<span class="badge ' . $class . '">' . ucfirst(e($row->status)) . '</span>';
                })
                ->addColumn('applied_date', function ($row) {
                    return $row->applied_at?->format('M d, Y h:i A')
                        ?? $row->created_at->format('M d, Y h:i A');
                })
                ->addColumn('action', function ($row) {
                    $btns = '<div class="d-flex gap-1 justify-content-center">';

                    // View button
                    $btns .= '<a href="' . route('admin.spotlight.applications.show', $row->id) . '" class="btn btn-sm btn-soft-info" title="View"><i class="ri-eye-line"></i></a>';

                    // Quick approve (only for pending with an active week)
                    if ($row->isPending() && $row->week && in_array($row->week->status, ['pending', 'nominating'])) {
                        $btns .= '
                            <form action="' . route('admin.spotlight.applications.approve', $row->id) . '" method="POST" class="d-inline" data-confirm="Approve this application and create a nominee record?" data-confirm-type="confirm">
                                ' . csrf_field() . '
                                <button type="submit" class="btn btn-sm btn-soft-success" title="Approve & Add as Nominee"><i class="ri-check-double-line"></i></button>
                            </form>
                        ';
                    }

                    // Quick reject (only for pending)
                    if ($row->isPending()) {
                        $btns .= '
                            <form action="' . route('admin.spotlight.applications.update-status', $row->id) . '" method="POST" class="d-inline" data-confirm="Reject this application? The applicant can re-apply to future weeks." data-confirm-type="danger">
                                ' . csrf_field() . '
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-soft-danger" title="Reject"><i class="ri-close-line"></i></button>
                            </form>
                        ';
                    }

                    // Revert to pending (for selected/rejected)
                    if (in_array($row->status, ['selected', 'rejected'])) {
                        $btns .= '
                            <form action="' . route('admin.spotlight.applications.update-status', $row->id) . '" method="POST" class="d-inline" data-confirm="Revert this application back to pending?" data-confirm-type="warning">
                                ' . csrf_field() . '
                                <input type="hidden" name="status" value="pending">
                                <button type="submit" class="btn btn-sm btn-soft-warning" title="Revert to Pending"><i class="ri-arrow-go-back-line"></i></button>
                            </form>
                        ';
                    }

                    $btns .= '</div>';
                    return $btns;
                })
                ->rawColumns(['applicant', 'spotlight_type', 'status_badge', 'action'])
                ->make(true);
        }

        return view('web.admin.spotlight.applications.index', compact('stats'));
    }

    /**
     * Display the specified application details.
     */
    public function show(SpotlightApplication $application)
    {
        $application->load([
            'week',
            'user.profile',
            'spotlightable',
            'reviewer.profile',
        ]);

        // Check if this application already has a nominee record
        $existingNominee = SpotlightWeekNominee::where('spotlight_week_id', $application->spotlight_week_id)
            ->where('spotlightable_type', $application->spotlightable_type)
            ->where('spotlightable_id', $application->spotlightable_id)
            ->first();

        return view('web.admin.spotlight.applications.show', compact('application', 'existingNominee'));
    }

    /**
     * Approve a pending application — marks it as selected and creates a nominee record.
     */
    public function approve(Request $request, SpotlightApplication $application)
    {
        if (!$application->isPending()) {
            return redirect()->back()->with('error', 'Only pending applications can be approved.');
        }

        $week = $application->week;
        if (!$week || !in_array($week->status, ['pending', 'nominating'])) {
            return redirect()->back()->with('error', 'The associated week is not accepting nominees.');
        }

        $validated = $request->validate([
            'reviewer_notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($application, $week, $validated) {
            // Mark application as selected
            $application->update([
                'status'         => 'selected',
                'reviewed_at'    => now(),
                'reviewed_by'    => auth('admin')->id(),
                'reviewer_notes' => $validated['reviewer_notes'] ?? $application->reviewer_notes,
            ]);

            // Create nominee record if it doesn't already exist
            SpotlightWeekNominee::firstOrCreate(
                [
                    'spotlight_week_id'  => $week->id,
                    'spotlightable_type' => $application->spotlightable_type,
                    'spotlightable_id'   => $application->spotlightable_id,
                ],
                [
                    'user_id'          => $application->user_id,
                    'free_vote_count'  => 0,
                    'paid_vote_count'  => 0,
                    'total_vote_count' => 0,
                    'is_winner'        => false,
                ]
            );
        });

        return redirect()->route('admin.spotlight.applications.show', $application->id)
            ->with('success', 'Application approved and nominee record created successfully.');
    }

    /**
     * Update application status (reject, revert to pending, or withdraw).
     */
    public function updateStatus(Request $request, SpotlightApplication $application)
    {
        $validated = $request->validate([
            'status'         => 'required|in:pending,rejected',
            'reviewer_notes' => 'nullable|string|max:1000',
        ]);

        // Prevent reverting selected applications that already have nominees
        if ($validated['status'] === 'pending' && $application->isSelected()) {
            $hasNominee = SpotlightWeekNominee::where('spotlight_week_id', $application->spotlight_week_id)
                ->where('spotlightable_type', $application->spotlightable_type)
                ->where('spotlightable_id', $application->spotlightable_id)
                ->exists();

            if ($hasNominee) {
                return redirect()->back()->with('error', 'Cannot revert to pending because a nominee record already exists. Remove the nominee first.');
            }
        }

        $application->update([
            'status'         => $validated['status'],
            'reviewed_at'    => now(),
            'reviewed_by'    => auth('admin')->id(),
            'reviewer_notes' => $validated['reviewer_notes'] ?? $application->reviewer_notes,
        ]);

        $message = match ($validated['status']) {
            'rejected' => 'Application rejected successfully.',
            'pending'  => 'Application reverted to pending successfully.',
            default    => 'Application status updated successfully.',
        };

        return redirect()->route('admin.spotlight.applications.show', $application->id)
            ->with('success', $message);
    }
}
