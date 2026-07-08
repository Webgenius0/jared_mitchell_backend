<?php

namespace App\Http\Controllers\Web\Admin\Round;

use App\Http\Controllers\Controller;
use App\Models\Contest\Season;
use App\Models\Round;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoundSessionController extends Controller
{
    /**
     * Display a listing of seasons.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Season::withCount('rounds')->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('title', fn(Season $season) => '<strong>' . e($season->title) . '</strong>')
                ->addColumn('status', function (Season $season) {
                    if (!$season->is_active) {
                        return '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';
                    }

                    $now = now();
                    if ($season->starts_at && $now < $season->starts_at) {
                        return '<span class="badge bg-info-subtle text-info">Upcoming</span>';
                    }
                    if ($season->ends_at && $now > $season->ends_at) {
                        return '<span class="badge bg-dark-subtle text-dark">Ended</span>';
                    }
                    return '<span class="badge bg-success-subtle text-success">Active</span>';
                })
                ->addColumn('rounds_count', fn(Season $season) => (int) $season->rounds_count)
                ->addColumn('date_range', function (Season $season) {
                    $start = $season->starts_at?->format('M d, Y') ?? '—';
                    $end = $season->ends_at?->format('M d, Y') ?? '—';
                    return '<span class="text-nowrap">' . e($start) . ' - ' . e($end) . '</span>';
                })
                ->addColumn('action', function (Season $season) {
                    $editBtn = '<a href="' . route('admin.round-sessions.edit', $season->id) . '" class="btn btn-sm btn-soft-primary" title="Edit"><i class="ri-pencil-line"></i></a>';
                    $deleteBtn = '<button class="btn btn-sm btn-soft-danger delete-btn" data-id="' . $season->id . '" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                    return '<div class="d-flex gap-1 justify-content-center">' . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['title', 'status', 'date_range', 'action'])
                ->make(true);
        }

        return view('web.admin.rounds.index');
    }

    /**
     * Show the form for creating a new season.
     */
    public function create()
    {
        return view('web.admin.rounds.create');
    }

    /**
     * Store a newly created season in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:seasons,slug',
            'description' => 'nullable|string',
            'contest_type' => 'sometimes|string|max:50',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'applications_starts_at' => 'nullable|date',
            'applications_ends_at' => 'nullable|date|after_or_equal:applications_starts_at',
            'rounds' => 'nullable|array',
            'rounds.*.round_number' => 'required|integer|min:1',
            'rounds.*.title' => 'required|string|max:255',
            'rounds.*.goal' => 'nullable|string',
            'rounds.*.requirements' => 'nullable|string',
            'rounds.*.advance_limit' => 'nullable|integer|min:1',
            'rounds.*.starts_at' => 'nullable|date',
            'rounds.*.ends_at' => 'nullable|date|after_or_equal:rounds.*.starts_at',
        ]);

        $data = $validated;
        $data['is_active'] = $request->boolean('is_active');
        unset($data['rounds']);

        $season = Season::create($data);

        // Create nested rounds
        if ($request->has('rounds')) {
            foreach ($request->rounds as $roundData) {
                $season->rounds()->create([
                    'round_number' => $roundData['round_number'],
                    'title' => $roundData['title'],
                    'goal' => $roundData['goal'] ?? null,
                    'requirements' => $roundData['requirements'] ?? null,
                    'is_active' => $roundData['is_active'] ?? false,
                    'advance_limit' => $roundData['advance_limit'] ?? null,
                    'starts_at' => $roundData['starts_at'] ?? null,
                    'ends_at' => $roundData['ends_at'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.round-sessions.index')
            ->with('success', 'Season created successfully.');
    }

    /**
     * Show the form for editing the specified season.
     */
    public function edit(Season $season)
    {
        $season->load('rounds');
        return view('web.admin.rounds.edit', compact('season'));
    }

    /**
     * Update the specified season in storage.
     */
    public function update(Request $request, Season $season)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:seasons,slug,' . $season->id,
            'description' => 'nullable|string',
            'contest_type' => 'sometimes|string|max:50',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'applications_starts_at' => 'nullable|date',
            'applications_ends_at' => 'nullable|date|after_or_equal:applications_starts_at',
            'rounds' => 'nullable|array',
            'rounds.*.id' => 'nullable|exists:rounds,id',
            'rounds.*.round_number' => 'required|integer|min:1',
            'rounds.*.title' => 'required|string|max:255',
            'rounds.*.goal' => 'nullable|string',
            'rounds.*.requirements' => 'nullable|string',
            'rounds.*.advance_limit' => 'nullable|integer|min:1',
            'rounds.*.starts_at' => 'nullable|date',
            'rounds.*.ends_at' => 'nullable|date|after_or_equal:rounds.*.starts_at',
        ]);

        $data = $validated;
        $data['is_active'] = $request->boolean('is_active');
        unset($data['rounds']);

        $season->update($data);

        // Sync rounds: delete removed ones, update existing, create new
        $existingIds = collect($request->rounds)->pluck('id')->filter()->toArray();
        $season->rounds()->whereNotIn('id', $existingIds)->delete();

        if ($request->has('rounds')) {
            foreach ($request->rounds as $roundData) {
                $roundPayload = [
                    'round_number' => $roundData['round_number'],
                    'title' => $roundData['title'],
                    'goal' => $roundData['goal'] ?? null,
                    'requirements' => $roundData['requirements'] ?? null,
                    'is_active' => $roundData['is_active'] ?? false,
                    'advance_limit' => $roundData['advance_limit'] ?? null,
                    'starts_at' => $roundData['starts_at'] ?? null,
                    'ends_at' => $roundData['ends_at'] ?? null,
                ];

                if (!empty($roundData['id'])) {
                    $season->rounds()->where('id', $roundData['id'])->update($roundPayload);
                } else {
                    $season->rounds()->create($roundPayload);
                }
            }
        }

        return redirect()->route('admin.round-sessions.index')
            ->with('success', 'Season updated successfully.');
    }

    /**
     * Remove the specified season from storage.
     */
    public function destroy(Season $season)
    {
        $season->rounds()->delete();
        $season->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Season deleted successfully.',
            ]);
        }

        return redirect()->route('admin.round-sessions.index')
            ->with('success', 'Season deleted successfully.');
    }
}
