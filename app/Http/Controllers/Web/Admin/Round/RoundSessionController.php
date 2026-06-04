<?php

namespace App\Http\Controllers\Web\Admin\Round;

use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Models\RoundSession;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoundSessionController extends Controller
{
    /**
     * Display a listing of round sessions.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = RoundSession::withCount('rounds')->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('title', fn(RoundSession $session) => '<strong>' . e($session->title) . '</strong>')
                ->addColumn('status', function (RoundSession $session) {
                    if (!$session->is_active) {
                        return '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';
                    }

                    $now = now();
                    if ($session->starts_at && $now < $session->starts_at) {
                        return '<span class="badge bg-info-subtle text-info">Upcoming</span>';
                    }
                    if ($session->ends_at && $now > $session->ends_at) {
                        return '<span class="badge bg-dark-subtle text-dark">Ended</span>';
                    }
                    return '<span class="badge bg-success-subtle text-success">Active</span>';
                })
                ->addColumn('rounds_count', fn(RoundSession $session) => (int) $session->rounds_count)
                ->addColumn('date_range', function (RoundSession $session) {
                    $start = $session->starts_at?->format('M d, Y') ?? '—';
                    $end = $session->ends_at?->format('M d, Y') ?? '—';
                    return '<span class="text-nowrap">' . e($start) . ' - ' . e($end) . '</span>';
                })
                ->addColumn('action', function (RoundSession $session) {
                    $editBtn = '<a href="' . route('admin.round-sessions.edit', $session->id) . '" class="btn btn-sm btn-soft-primary" title="Edit"><i class="ri-pencil-line"></i></a>';
                    $deleteBtn = '<button class="btn btn-sm btn-soft-danger delete-btn" data-id="' . $session->id . '" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                    return '<div class="d-flex gap-1 justify-content-center">' . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['title', 'status', 'date_range', 'action'])
                ->make(true);
        }

        return view('web.admin.rounds.index');
    }

    /**
     * Show the form for creating a new round session.
     */
    public function create()
    {
        return view('web.admin.rounds.create');
    }

    /**
     * Store a newly created round session in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:round_sessions,slug',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
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

        $session = RoundSession::create($data);

        // Create nested rounds
        if ($request->has('rounds')) {
            foreach ($request->rounds as $roundData) {
                $session->rounds()->create([
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
            ->with('success', 'Round session created successfully.');
    }

    /**
     * Show the form for editing the specified round session.
     */
    public function edit(RoundSession $roundSession)
    {
        $roundSession->load('rounds');
        return view('web.admin.rounds.edit', compact('roundSession'));
    }

    /**
     * Update the specified round session in storage.
     */
    public function update(Request $request, RoundSession $roundSession)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:round_sessions,slug,' . $roundSession->id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
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

        $roundSession->update($data);

        // Sync rounds: delete removed ones, update existing, create new
        $existingIds = collect($request->rounds)->pluck('id')->filter()->toArray();
        $roundSession->rounds()->whereNotIn('id', $existingIds)->delete();

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
                    $roundSession->rounds()->where('id', $roundData['id'])->update($roundPayload);
                } else {
                    $roundSession->rounds()->create($roundPayload);
                }
            }
        }

        return redirect()->route('admin.round-sessions.index')
            ->with('success', 'Round session updated successfully.');
    }

    /**
     * Remove the specified round session from storage.
     */
    public function destroy(RoundSession $roundSession)
    {
        $roundSession->rounds()->delete();
        $roundSession->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Round session deleted successfully.',
            ]);
        }

        return redirect()->route('admin.round-sessions.index')
            ->with('success', 'Round session deleted successfully.');
    }
}
