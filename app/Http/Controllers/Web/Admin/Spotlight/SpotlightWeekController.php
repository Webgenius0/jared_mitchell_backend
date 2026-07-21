<?php

namespace App\Http\Controllers\Web\Admin\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\Spotlight\SpotlightApplication;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SpotlightWeekController extends Controller
{
    /**
     * Display a listing of spotlight weeks.
     */
    public function index(Request $request)
    {        $stats = [
            'total'      => SpotlightWeek::count(),
            'pending'    => SpotlightWeek::where('status', 'pending')->count(),
            'nominating' => SpotlightWeek::where('status', 'nominating')->count(),
            'voting'     => SpotlightWeek::where('status', 'voting')->count(),
            'completed'  => SpotlightWeek::where('status', 'completed')->count(),
            'cancelled'  => SpotlightWeek::where('status', 'cancelled')->count(),
        ];

        $editWeekId = $request->query('edit');

        if ($request->ajax()) {
            $query = SpotlightWeek::withCount('nominees', 'applications')->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('week_label', function ($row) {
                    return 'Week ' . e($row->week_number) . ' (' . e($row->year) . ')';
                })
                ->addColumn('status_badge', function ($row) {
                    $map = [
                        'pending' => 'bg-warning-subtle text-warning',
                        'nominating' => 'bg-info-subtle text-info',
                        'voting' => 'bg-success-subtle text-success',
                        'completed' => 'bg-primary-subtle text-primary',
                        'cancelled' => 'bg-danger-subtle text-danger',
                    ];
                    $class = $map[$row->status] ?? 'bg-secondary-subtle text-secondary';
                    return '<span class="badge ' . $class . '">' . ucfirst(e($row->status)) . '</span>';
                })
                ->addColumn('voting_window', function ($row) {
                    $start = $row->voting_starts_at?->format('M d, Y h:i A') ?? '—';
                    $end = $row->voting_ends_at?->format('M d, Y h:i A') ?? '—';
                    return '<span class="text-nowrap">' . e($start) . ' — ' . e($end) . '</span>';
                })
                ->addColumn('nominees_count', function ($row) {
                    return (int) $row->nominees_count;
                })
                ->addColumn('action', function ($row) {
                    $showBtn = '<a href="' . route('admin.spotlight.weeks.show', $row->id) . '" class="btn btn-sm btn-soft-info" title="View"><i class="ri-eye-line"></i></a>';
                    $editBtn = '<button class="btn btn-sm btn-soft-primary edit-week" data-id="' . $row->id . '" title="Edit"><i class="ri-pencil-line"></i></button>';
                    $deleteBtn = '
                        <form action="' . route('admin.spotlight.weeks.destroy', $row->id) . '" method="POST" class="d-inline" data-confirm="Delete Week ' . e($row->week_number) . ' (' . e($row->year) . ')? This cannot be undone." data-confirm-type="danger">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                        </form>';
                    return '<div class="d-flex gap-1 justify-content-center">' . $showBtn . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['status_badge', 'voting_window', 'action'])
                ->make(true);
        }

        return view('web.admin.spotlight.weeks.index', compact('stats', 'editWeekId'));
    }

    /**
     * Store a newly created spotlight week.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'week_number' => 'required|integer|min:1|max:53',
            'year' => 'required|integer|min:2020|max:2099',
            'voting_starts_at' => 'nullable|date',
            'voting_ends_at' => 'nullable|date|after_or_equal:voting_starts_at',
            'status' => 'required|in:pending,nominating,voting,voting_closed,completed,cancelled',
        ]);

        $validated['status'] = $validated['status'] ?? 'pending';

        SpotlightWeek::create($validated);

        return redirect()->route('admin.spotlight.weeks.index')
            ->with('success', 'Spotlight week created successfully.');
    }

    /**
     * Return week data for the edit modal.
     */
    public function edit(SpotlightWeek $week)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $week->id,
                'week_number' => $week->week_number,
                'year' => $week->year,
                'status' => $week->status,
                'voting_starts_at' => $week->voting_starts_at?->format('Y-m-d\TH:i'),
                'voting_ends_at' => $week->voting_ends_at?->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    /**
     * Update an existing spotlight week.
     */
    public function update(Request $request, SpotlightWeek $week)
    {
        $validated = $request->validate([
            'week_number' => 'required|integer|min:1|max:53',
            'year' => 'required|integer|min:2020|max:2099',
            'voting_starts_at' => 'nullable|date',
            'voting_ends_at' => 'nullable|date|after_or_equal:voting_starts_at',
            'status' => 'required|in:pending,nominating,voting,voting_closed,completed,cancelled',
        ]);

        $week->update($validated);

        return redirect()->route('admin.spotlight.weeks.index')
            ->with('success', 'Spotlight week updated successfully.');
    }

    /**
     * Delete a spotlight week.
     */
    public function destroy(SpotlightWeek $week)
    {
        if (in_array($week->status, ['voting', 'completed'])) {
            return redirect()->back()->with('error', 'Cannot delete a week that is in voting or completed status.');
        }

        $week->delete();

        return redirect()->route('admin.spotlight.weeks.index')
            ->with('success', 'Spotlight week deleted successfully.');
    }

    /**
     * Quick status change for a spotlight week.
     */
    public function updateStatus(Request $request, SpotlightWeek $week)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,nominating,voting,voting_closed,completed,cancelled',
        ]);

        $newStatus = $validated['status'];
        $current = $week->status;

        // Validate allowed transitions
        $allowed = [
            'pending' => ['nominating', 'cancelled'],
            'nominating' => ['pending', 'voting', 'cancelled'],
            'voting' => ['voting_closed', 'cancelled'],
            'voting_closed' => ['voting', 'completed', 'cancelled'],
            'completed' => [],
            'cancelled' => ['pending'],
        ];

        if (!in_array($newStatus, $allowed[$current] ?? [])) {
            return redirect()->back()->with('error', "Cannot change status from '{$current}' to '{$newStatus}'.");
        }

        // If transitioning to completed, require that a winner is set
        if ($newStatus === 'completed' && !$week->winner_spotlightable_id) {
            return redirect()->back()->with('error', 'Cannot mark as completed without a winner. Use "Announce Winner" instead.');
        }

        $week->update(['status' => $newStatus]);

        return redirect()->route('admin.spotlight.weeks.show', $week->id)
            ->with('success', "Week status changed to '{$newStatus}' successfully.");
    }

    /**
     * Show week details with nominees.
     */
    public function show(SpotlightWeek $week)
    {
        $week->load(['nominees.spotlightable', 'nominees.user.profile']);

        $nominations = $week->nominees()->orderByDesc('total_vote_count')->get();

        return view('web.admin.spotlight.weeks.show', compact('week', 'nominations'));
    }

    /**
     * Show applications for a specific week.
     */
    public function applications(SpotlightWeek $week)
    {
        $applications = $week->applications()
            ->with(['spotlightable', 'user.profile'])
            ->latest('applied_at')
            ->paginate(20);

        return view('web.admin.spotlight.weeks.applications', compact('week', 'applications'));
    }

    /**
     * Select nominees (Top N) from applications for a week.
     */
    public function selectNominees(Request $request, SpotlightWeek $week)
    {
        $validated = $request->validate([
            'nominee_ids'   => 'required|array|min:1',
            'nominee_ids.*' => 'exists:spotlight_applications,id',
        ]);

        if (!in_array($week->status, ['pending', 'nominating'])) {
            return redirect()->back()->with('error', 'This week is not accepting nominee selection.');
        }

        DB::transaction(function () use ($week, $validated) {
            // Mark applications as selected
            SpotlightApplication::whereIn('id', $validated['nominee_ids'])
                ->update(['status' => 'selected', 'reviewed_at' => now(), 'reviewed_by' => auth('admin')->id()]);

            // Reject the rest
            $week->applications()->whereNotIn('id', $validated['nominee_ids'])
                ->where('status', 'pending')
                ->update(['status' => 'rejected', 'reviewed_at' => now(), 'reviewed_by' => auth('admin')->id()]);

            // Create nominee records for selected applications (skip if already a nominee)
            $applications = SpotlightApplication::whereIn('id', $validated['nominee_ids'])->get();
            $existingSpotlightableIds = $week->nominees()
                ->whereIn('spotlightable_type', $applications->pluck('spotlightable_type')->unique())
                ->whereIn('spotlightable_id', $applications->pluck('spotlightable_id'))
                ->pluck('spotlightable_id')
                ->toArray();

            foreach ($applications as $app) {
                if (in_array($app->spotlightable_id, $existingSpotlightableIds)) {
                    continue;
                }
                $week->nominees()->create([
                    'spotlightable_type' => $app->spotlightable_type,
                    'spotlightable_id' => $app->spotlightable_id,
                    'user_id' => $app->user_id,
                    'free_vote_count' => 0,
                    'paid_vote_count' => 0,
                    'total_vote_count' => 0,
                ]);
            }

            // Move week to voting status
            $week->update(['status' => 'voting']);
        });

        return redirect()->route('admin.spotlight.weeks.show', $week->id)
            ->with('success', 'Nominees selected and voting opened successfully.');
    }

    /**
     * Force close voting for a week.
     */
    public function closeVoting(SpotlightWeek $week)
    {
        if ($week->status !== 'voting') {
            return redirect()->back()->with('error', 'Voting is not currently open for this week.');
        }

        $week->update([
            'status' => 'voting_closed',
            'voting_ends_at' => now(),
        ]);

        return redirect()->route('admin.spotlight.weeks.show', $week->id)
            ->with('success', 'Voting closed successfully.');
    }

    /**
     * Announce the winner for a week.
     */
    public function announceWinner(Request $request, SpotlightWeek $week)
    {
        $validated = $request->validate([
            'nominee_id' => 'required|exists:spotlight_week_nominees,id',
        ]);

        // Ensure the nominee belongs to this week
        $nominee = $week->nominees()->findOrFail($validated['nominee_id']);

        DB::transaction(function () use ($week, $nominee) {
            // Mark previous winner as not winner
            $week->nominees()->where('is_winner', true)->update(['is_winner' => false]);

            // Mark this nominee as winner
            $nominee->update(['is_winner' => true]);

            // Update week with winner info
            $week->update([
                'status' => 'completed',
                'winner_spotlightable_type' => $nominee->spotlightable_type,
                'winner_spotlightable_id' => $nominee->spotlightable_id,
                'announced_at' => now(),
            ]);

            // Update rank for all nominees
            $this->updateRanks($week);
        });

        return redirect()->route('admin.spotlight.weeks.show', $week->id)
            ->with('success', 'Winner announced successfully.');
    }

    /**
     * Cancel a spotlight week.
     */
    public function cancel(SpotlightWeek $week)
    {
        if (in_array($week->status, ['completed', 'cancelled'])) {
            return redirect()->back()->with('error', 'This week cannot be cancelled.');
        }

        $week->update(['status' => 'cancelled']);

        return redirect()->route('admin.spotlight.weeks.index')
            ->with('success', 'Spotlight week cancelled successfully.');
    }

    /**
     * Update ranks for all nominees in a week based on total votes.
     */
    private function updateRanks(SpotlightWeek $week): void
    {
        $nominees = $week->nominees()->orderByDesc('total_vote_count')->get();
        $rank = 1;
        foreach ($nominees as $nominee) {
            $nominee->update(['rank' => $rank]);
            $rank++;
        }
    }
}
