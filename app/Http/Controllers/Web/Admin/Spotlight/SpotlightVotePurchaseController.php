<?php

namespace App\Http\Controllers\Web\Admin\Spotlight;

use App\Http\Controllers\Controller;
use App\Models\Spotlight\SpotlightVotePackage;
use App\Models\Spotlight\SpotlightVotePurchase;
use App\Services\Spotlight\SpotlightVotePurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SpotlightVotePurchaseController extends Controller
{
    public function __construct(
        protected SpotlightVotePurchaseService $purchaseService,
    ) {}

    /**
     * Display a listing of vote purchases.
     */
    public function index(Request $request)
    {
        $stats = [
            'total' => SpotlightVotePurchase::count(),
            'pending' => SpotlightVotePurchase::where('status', SpotlightVotePurchase::STATUS_PENDING)->count(),
            'approved' => SpotlightVotePurchase::where('status', SpotlightVotePurchase::STATUS_APPROVED)->count(),
            'paid' => SpotlightVotePurchase::where('status', SpotlightVotePurchase::STATUS_PAID)->count(),
            'refunded' => SpotlightVotePurchase::where('status', SpotlightVotePurchase::STATUS_REFUNDED)->count(),
            'cancelled' => SpotlightVotePurchase::where('status', SpotlightVotePurchase::STATUS_CANCELLED)->count(),
        ];

        $packages = SpotlightVotePackage::ordered()->get();

        if ($request->ajax()) {
            $query = SpotlightVotePurchase::with(['nominee.spotlightable', 'user.profile', 'package'])
                ->latest();

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('package')) {
                $query->where('spotlight_vote_package_id', $request->package);
            }

            if ($request->filled('search_query')) {
                $search = $request->search_query;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user.profile', function ($qp) use ($search) {
                        $qp->where('name', 'like', "%{$search}%");
                    })->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('email', 'like', "%{$search}%");
                    })->orWhereHas('nominee.spotlightable', function ($qs) use ($search) {
                        $qs->where('business_name', 'like', "%{$search}%")
                          ->orWhere('brand_name', 'like', "%{$search}%")
                          ->orWhere('artist_stage_name', 'like', "%{$search}%")
                          ->orWhere('full_legal_name', 'like', "%{$search}%");
                    });
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
                    $isArtist = $spotlightable instanceof \App\Models\ArtistSpotlight;
                    $name = $isArtist
                        ? ($spotlightable->artist_stage_name ?? $spotlightable->full_legal_name)
                        : ($spotlightable->business_name ?? $spotlightable->owner_founder_name);
                    return e($name ?? '#' . $spotlightable->id);
                })
                ->addColumn('package_name', function ($row) {
                    return e($row->package?->name ?? $row->package ?? '—');
                })
                ->addColumn('package_votes', function ($row) {
                    return $row->votes_count;
                })
                ->addColumn('amount_formatted', function ($row) {
                    return '$' . number_format($row->amount_paid, 2);
                })
                ->addColumn('status_badge', function ($row) {
                    $map = [
                        'pending' => 'bg-warning-subtle text-warning',
                        'approved' => 'bg-info-subtle text-info',
                        'paid' => 'bg-success-subtle text-success',
                        'refunded' => 'bg-danger-subtle text-danger',
                        'cancelled' => 'bg-secondary-subtle text-secondary',
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

        return view('web.admin.spotlight.vote-purchases.index', compact('stats', 'packages'));
    }

    /**
     * Get pending count (for dashboard widget).
     */
    public function pendingCount()
    {
        $count = SpotlightVotePurchase::where('status', SpotlightVotePurchase::STATUS_PENDING)->count();

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
        $purchase->load(['nominee.spotlightable', 'user.profile', 'approver.profile', 'order', 'package']);

        return view('web.admin.spotlight.vote-purchases.show', compact('purchase'));
    }

    /**
     * Approve a pending purchase — changes status to 'approved'.
     *
     * The user must then pay via Stripe before votes are credited.
     */
    public function approve(Request $request, SpotlightVotePurchase $purchase)
    {
        if (!$purchase->isPending()) {
            return redirect()->back()->with('error', 'This purchase is not in pending status.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // Admin user model for the approval
        $admin = auth('admin')->user();

        $result = $this->purchaseService->approvePurchase($purchase, $admin);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        // Add admin notes if provided
        if (!empty($validated['admin_notes'])) {
            $purchase->update(['admin_notes' => $validated['admin_notes']]);
        }

        return redirect()->route('admin.spotlight.vote-purchases.show', $purchase->id)
            ->with('success', 'Purchase approved. The user can now proceed to payment via Stripe.');
    }

    /**
     * Refund a purchase — remove votes from the nominee.
     *
     * Handles both paid purchases (refund with vote removal)
     * and pending/approved purchases (cancel without vote removal).
     */
    public function refund(Request $request, SpotlightVotePurchase $purchase)
    {
        if ($purchase->isRefunded()) {
            return redirect()->back()->with('error', 'This purchase has already been refunded.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $admin = auth('admin')->user();

        if ($purchase->isPaid()) {
            // Refund — remove votes
            $result = $this->purchaseService->refundPurchase($purchase, $admin, $validated['admin_notes'] ?? null);

            if (! $result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            return redirect()->route('admin.spotlight.vote-purchases.show', $purchase->id)
                ->with('success', $result['message']);
        }

        // Cancel pending/approved purchase (no votes to remove)
        $purchase->update([
            'status'      => SpotlightVotePurchase::STATUS_CANCELLED,
            'admin_notes' => $validated['admin_notes'] ?? $purchase->admin_notes,
        ]);

        return redirect()->route('admin.spotlight.vote-purchases.show', $purchase->id)
            ->with('success', 'Purchase cancelled successfully.');
    }
}
