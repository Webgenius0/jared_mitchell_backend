<?php

namespace App\Http\Controllers\Web\Admin\Round;

use App\Http\Controllers\Controller;
use App\Models\Contest\Season;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoundSeasonController extends Controller
{
    /**
     * Build advancement_config array from individual form fields.
     * Filters out empty values & converts types.
     */
    private function buildAdvancementConfig(?array $advConfig): ?array
    {
        if (empty($advConfig)) {
            return null;
        }

        // Remove null / empty-string values
        $config = array_filter($advConfig, fn($v) => $v !== null && $v !== '');

        if (empty($config)) {
            return null;
        }

        // Filter out empty tie_breakers values
        if (isset($config['tie_breakers']) && is_array($config['tie_breakers'])) {
            $config['tie_breakers'] = array_values(array_filter($config['tie_breakers'], fn($v) => trim($v) !== ''));
            if (empty($config['tie_breakers'])) {
                unset($config['tie_breakers']);
            }
        }

        // Convert categories from newline-separated string to array
        if (isset($config['categories']) && is_string($config['categories'])) {
            $cats = array_map('trim', explode("\n", $config['categories']));
            $cats = array_filter($cats, fn($c) => $c !== '');
            $config['categories'] = array_values($cats);
        }

        // Cast numeric fields
        foreach (['advance_limit', 'eliminate_count', 'keep_percent', 'score_threshold', 'max_votes_per_user', 'max_score_per_category'] as $numField) {
            if (isset($config[$numField])) {
                $config[$numField] = (int) $config[$numField];
            }
        }
        if (isset($config['vote_weight'])) {
            $config['vote_weight'] = (float) $config['vote_weight'];
        }

        return $config;
    }
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
                    $isActive = $season->is_active;
                    $toggleIcon = $isActive ? 'ri-pause-circle-line' : 'ri-play-circle-line';
                    $toggleClass = $isActive ? 'btn-soft-success' : 'btn-soft-warning';
                    $toggleTitle = $isActive ? 'Deactivate' : 'Activate';

                    $toggleBtn = '<button class="btn btn-sm ' . $toggleClass . ' toggle-active-btn" data-id="' . $season->id . '" data-active="' . ($isActive ? '1' : '0') . '" title="' . $toggleTitle . '"><i class="' . $toggleIcon . '"></i></button>';
                    $editBtn = '<a href="' . route('admin.round-sessions.edit', $season->id) . '" class="btn btn-sm btn-soft-primary" title="Edit"><i class="ri-pencil-line"></i></a>';
                    $deleteBtn = '<button class="btn btn-sm btn-soft-danger delete-btn" data-id="' . $season->id . '" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                    return '<div class="d-flex gap-1 justify-content-center">' . $toggleBtn . $editBtn . $deleteBtn . '</div>';
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
            'rounds.*.adv_config' => 'nullable|array',
            'rounds.*.adv_config.tie_breakers' => 'nullable|array',
            'rounds.*.adv_config.tie_breakers.*' => 'nullable|string|max:255',
            'rounds.*.voting_ends_at' => 'nullable|date',
            'rounds.*.is_active' => 'nullable|boolean',
            'rounds.*.starts_at' => 'nullable|date',
            'rounds.*.ends_at' => 'nullable|date|after_or_equal:rounds.*.starts_at',
        ]);

        $data = $validated;
        $data['is_active'] = $request->boolean('is_active');
        unset($data['rounds']);

        // If activating, deactivate all other seasons first
        if ($data['is_active']) {
            Season::where('is_active', true)->update(['is_active' => false]);
        }

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
                    'advancement_config' => $this->buildAdvancementConfig($roundData['adv_config'] ?? null),
                    'voting_ends_at' => $roundData['voting_ends_at'] ?? null,
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
            'rounds.*.adv_config' => 'nullable|array',
            'rounds.*.adv_config.tie_breakers' => 'nullable|array',
            'rounds.*.adv_config.tie_breakers.*' => 'nullable|string|max:255',
            'rounds.*.voting_ends_at' => 'nullable|date',
            'rounds.*.is_active' => 'nullable|boolean',
            'rounds.*.starts_at' => 'nullable|date',
            'rounds.*.ends_at' => 'nullable|date|after_or_equal:rounds.*.starts_at',
        ]);

        $data = $validated;
        $data['is_active'] = $request->boolean('is_active');
        unset($data['rounds']);

        // If activating, deactivate all other seasons first
        if ($data['is_active']) {
            Season::where('id', '!=', $season->id)->where('is_active', true)->update(['is_active' => false]);
        }

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
                    'advancement_config' => $this->buildAdvancementConfig($roundData['adv_config'] ?? null),
                    'voting_ends_at' => $roundData['voting_ends_at'] ?? null,
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
     * Toggle the active status of a season.
     * Only one season can be active at a time.
     */
    public function toggleActive(Season $season)
    {
        if ($season->is_active) {
            $season->update(['is_active' => false]);
            $message = 'Season deactivated successfully.';
        } else {
            // Deactivate all other seasons, then activate this one
            Season::where('id', '!=', $season->id)->where('is_active', true)->update(['is_active' => false]);
            $season->update(['is_active' => true]);
            $message = 'Season activated successfully.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
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
