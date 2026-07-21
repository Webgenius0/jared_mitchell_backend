<?php

namespace App\Http\Controllers\Web\Admin\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\Spotlight\SpotlightVotePurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SpotlightVotePurchaseController extends Controller
{
    /**
     * Display a listing of vote purchases.
     */
    public function index(Request $request)
    {
        $stats = [
            'total'    => SpotlightVotePurchase::count(),
            'pending'  => SpotlightVotePurchase::where('status', 'pending')->count(),
            'completed'=> SpotlightVotePurchase::where('status', 'completed')->count(),
            'refunded' => SpotlightVotePurchase::where('status', 'refunded')->count(),
        ];

        if ($request->ajax()) {
            $query = SpotlightVotePurchase::with(['nominee.spotlightable', 'user.profile'])
                ->latest();

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('package')) {
                $query->where('package', $request->package);
            }

            if ($request->filled('search_query')) {
                $search = $request->search_query;
                $query->whereHas('user.profile', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('nominee.spotlightable', function ($q) use ($search) {
                    $q->where('business_name', 'like', "%{$search}%")
                      ->orWhere('brand_name', 'like', "%{$search}%");
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return e($row->user?->profile?->name ?? $row->user?->email ?? '—');
                })
                ->addColumn('nominee_name', function ($row) {
                    $spotlightable = $row->nominee?->spotlightable;
                    if (!$spotlightable) return '—';
                    $name = $spotlightable->business_name ?? $spotlightable->brand_name ?? '#' . $spotlightable->id;
                    return e($name);
                })
                ->addColumn('package_label', function ($row) {
                    $details = SpotlightVotePurchase::packageDetails($row->package);
                    return e($details['label'] ?? $row->package);
                })
                ->addColumn('amount_formatted', function ($row) {
                    return '$' . number_format($row->amount_paid, 2);
                })
                ->addColumn('status_badge', function ($row) {
                    $map = [
                        'pending'   => 'bg-warning-subtle text-warning',
                        'completed' => 'bg-success-subtle text-success',
                        'refunded'  => 'bg-danger-subtle text-danger',
                    ];
                    $class = $map[$row->status] ?? 'bg-secondary-subtle text-secondary';
                    return '<span class="badge ' . $class . '">' . ucfirst(e($row->status)) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $showBtn = '<a href="' . route('admin.spotlight.vote-purchases.show', $row->id) . '" class="btn btn-sm btn-soft-info" title="View"><i class="ri-eye-line"></i></a>';
                    return '<div class="d-flex gap-1 justify-content-center">' . $showBtn . '</div>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('web.admin.spotlight.vote-purchases.index', compact('stats'));
    }

    /**
     * Get pending count (for dashboard widget).
     */
    public function pendingCount()
    {
        $count = SpotlightVotePurchase::where('status', 'pending')->count();

        return response()->json([
            'success' => true,
            'count'   => $count,
        ]);
    }

    /**
     * Show purchase details.
     */
    public function show(SpotlightVotePurchase $purchase)
    {
        $purchase->load(['nominee.spotlightable', 'user.profile', 'approver.profile', 'order']);

        return view('web.admin.spotlight.vote-purchases.show', compact('purchase'));
    }

    /**
     * Approve a pending purchase — credit votes to the nominee.
     */
    public function approve(Request $request, SpotlightVotePurchase $purchase)
    {
        if (!$purchase->isPending()) {
            return redirect()->back()->with('error', 'This purchase is not in pending status.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($purchase, $validated) {
            // Credit votes to the nominee
            $purchase->nominee->addPaidVotes($purchase->votes_count);

            // Mark purchase as completed
            $purchase->update([
                'status'       => 'completed',
                'approved_by'  => auth('admin')->id(),
                'approved_at'  => now(),
                'admin_notes'  => $validated['admin_notes'] ?? $purchase->admin_notes,
            ]);
        });

        return redirect()->route('admin.spotlight.vote-purchases.show', $purchase->id)
            ->with('success', 'Purchase approved and votes credited successfully.');
    }

    /**
     * Refund a purchase — remove votes from the nominee.
     */
    public function refund(Request $request, SpotlightVotePurchase $purchase)
    {
        if ($purchase->status === 'refunded') {
            return redirect()->back()->with('error', 'This purchase has already been refunded.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($purchase, $validated) {
            // Remove votes from the nominee (if already credited)
            if ($purchase->isCompleted()) {
                $purchase->nominee->removePaidVotes($purchase->votes_count);
            }

            // Mark purchase as refunded
            $purchase->update([
                'status'      => 'refunded',
                'approved_by' => auth('admin')->id(),
                'approved_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? $purchase->admin_notes,
            ]);
        });

        return redirect()->route('admin.spotlight.vote-purchases.show', $purchase->id)
            ->with('success', 'Purchase refunded and votes removed successfully.');
    }
}
