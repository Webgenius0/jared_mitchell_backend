<?php

namespace App\Http\Controllers\Web\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Business;
use App\Models\BusinessSpotlight;
use App\Models\ArtistSpotlight;
use App\Models\Contact;
use App\Models\Newsletter;
use App\Models\Contest\Season;
use App\Models\ContestApplication;
use App\Models\Spotlight\SpotlightVotePackage;
use App\Models\Spotlight\SpotlightVotePurchase;
use App\Models\Spotlight\SpotlightWeek;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Show Admin Dashboard with real data.
     */
    public function index()
    {
        $admin = auth('admin')->user();

        // ── Core Stats ─────────────────────────────────────────
        $stats = [
            // Users
            'total_users'        => User::count(),
            'active_users'       => User::where('status', 'active')->count(),
            'artist_users'       => User::role('artist', 'api')->count(),
            'business_users'     => User::role('boss', 'api')->count(),
            'member_users'       => User::role('member', 'api')->count(),
            'sponsor_users'      => User::role('sponsor', 'api')->count(),

            // Orders & Revenue
            'total_orders'       => Order::count(),
            'paid_orders'        => Order::where('payment_status', 'paid')->count(),
            'pending_orders'     => Order::where('payment_status', 'unpaid')->count(),
            'total_revenue'      => Order::where('payment_status', 'paid')->sum('total'),
            'today_revenue'      => Order::where('payment_status', 'paid')
                                    ->whereDate('created_at', today())->sum('total'),

            // Products
            'total_products'     => Product::count(),
            'active_products'    => Product::where('is_active', true)->count(),

            // Events
            'total_events'       => Event::count(),
            'published_events'   => Event::where('status', 'published')->count(),
            'upcoming_events'    => Event::where('status', 'published')
                                    ->where('starts_at', '>=', now())->count(),

            // Event Registrations
            'total_registrations' => EventRegistration::count(),
            'confirmed_registrations' => EventRegistration::where('status', 'confirmed')->count(),

            // Businesses
            'total_businesses'   => Business::count(),
            'active_businesses'  => Business::where('status', 'active')->count(),

            // Spotlights
            'total_business_spotlights'  => BusinessSpotlight::count(),
            'pending_business_spotlights'=> BusinessSpotlight::where(fn($q) => $q->where('status', 'submitted')->orWhere('status', 'under_review'))->count(),
            'total_artist_spotlights'    => ArtistSpotlight::count(),
            'pending_artist_spotlights'  => ArtistSpotlight::where(fn($q) => $q->where('status', 'submitted')->orWhere('status', 'under_review'))->count(),

            // Contest
            'total_seasons'      => Season::count(),
            'active_seasons'     => Season::where('is_active', true)->count(),
            'contest_applications' => ContestApplication::count(),
            'pending_applications' => ContestApplication::where('status', 'pending')->count(),

            // Contacts
            'total_contacts'     => Contact::count(),
            'unread_contacts'    => Contact::where('status', 'pending')->count(),

            // Newsletter
            'total_subscribers'  => Newsletter::where('status', 'active')->count(),

            // Spotlight Voting
            'active_spotlight_weeks' => SpotlightWeek::where('status', 'voting')->count(),
            'pending_spotlight_purchases' => SpotlightVotePurchase::where('status', 'pending')->count(),
            'completed_spotlight_purchases' => SpotlightVotePurchase::where('status', SpotlightVotePurchase::STATUS_PAID)->count(),
            'refunded_spotlight_purchases' => SpotlightVotePurchase::where('status', SpotlightVotePurchase::STATUS_REFUNDED)->count(),
            'total_spotlight_purchases' => SpotlightVotePurchase::count(),
            'spotlight_purchase_revenue' => SpotlightVotePurchase::where('status', SpotlightVotePurchase::STATUS_PAID)->sum('amount_paid'),
        ];

        // ── Vote Purchase Package Breakdown ──────────────────────
        $votePackageBreakdown = SpotlightVotePurchase::selectRaw('COALESCE(spotlight_vote_package_id, 0) as pkg_id, COUNT(*) as total, SUM(votes_count) as votes, SUM(amount_paid) as revenue')
            ->where('status', SpotlightVotePurchase::STATUS_PAID)
            ->groupBy('pkg_id')
            ->get()
            ->keyBy('pkg_id');

        // ── Recent Pending Vote Purchases ────────────────────────
        $recentPendingPurchases = SpotlightVotePurchase::with(['user.profile', 'nominee.spotlightable'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($purchase) {
                $spotlightable = $purchase->nominee?->spotlightable;
                return [
                    'id'          => $purchase->id,
                    'user_name'   => $purchase->user?->profile?->name ?? $purchase->user?->email ?? '—',
                    'nominee_name'=> $spotlightable?->business_name ?? $spotlightable?->brand_name ?? '—',
                    'package'     => $purchase->package,
                    'votes'       => $purchase->votes_count,
                    'amount'      => $purchase->amount_paid,
                    'created_at'  => $purchase->created_at,
                ];
            });

        // ── Recent Orders ───────────────────────────────────────
        $recentOrders = Order::with('user.profile')
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'order_number'   => $order->order_number,
                    'customer_name'  => $order->user?->profile?->name ?? $order->user?->email ?? 'Guest',
                    'customer_avatar'=> $order->user?->profile?->avatar_url ?? null,
                    'total'          => $order->total,
                    'status'         => $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at'     => $order->created_at,
                ];
            });

        // ── Recent Contacts ─────────────────────────────────────
        $recentContacts = Contact::latest()->take(5)->get()->map(function ($contact) {
            return [
                'id'         => $contact->id,
                'name'       => $contact->first_name . ' ' . $contact->last_name,
                'email'      => $contact->email,
                'subject'    => $contact->subject,
                'status'     => $contact->status,
                'created_at' => $contact->created_at,
            ];
        });

        // ── Chart Data: Orders by month (configurable range) ──
        $allowedRanges = [3, 6, 12];
        $selectedRange = (int) request('range', 6);
        if (!in_array($selectedRange, $allowedRanges)) {
            $selectedRange = 6;
        }

        $months = collect();
        for ($i = $selectedRange - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'label' => $date->format('M Y'),
                'orders' => Order::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'revenue' => Order::where('payment_status', 'paid')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('total'),
            ]);
        }

        return view('web.dashboard.dashboard', compact(
            'admin',
            'stats',
            'recentOrders',
            'recentContacts',
            'months',
            'votePackageBreakdown',
            'recentPendingPurchases',
            'selectedRange',
            'allowedRanges'
        ) + [
            'votePackages' => SpotlightVotePackage::active()->ordered()->get(),
        ]);
    }
}
