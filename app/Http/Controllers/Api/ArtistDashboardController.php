<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArtistSpotlight;
use App\Models\EventRegistration;
use App\Models\Spotlight\SpotlightVote;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ArtistDashboardController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/artist/dashboard/stats
     *
     * Returns top-level statistics and single-variable monthly performance for artists.
     */
    public function stats(): JsonResponse
    {
        $userId = auth('api')->id();

        // 1. Stats
        $totalSpotlight = ArtistSpotlight::where('user_id', $userId)->count();
        $approvedSpotlight = ArtistSpotlight::where('user_id', $userId)->where('status', 'approved')->count();
        
        $ticketPurchasing = EventRegistration::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->count();

        // 2. Spotlight Performance (Monthly Votes for Line Chart)
        $spotlightIds = ArtistSpotlight::where('user_id', $userId)->pluck('id');
        
        $nomineeIds = SpotlightWeekNominee::where('spotlightable_type', \App\Models\ArtistSpotlight::class)
            ->whereIn('spotlightable_id', $spotlightIds)
            ->pluck('id');

        $monthlyVotes = SpotlightVote::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month_num, COUNT(*) as count')
            ->groupBy('month_num')
            ->pluck('count', 'month_num');

        // Including paid votes
        $monthlyPaidVotes = \App\Models\Spotlight\SpotlightVotePurchase::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->where('status', \App\Models\Spotlight\SpotlightVotePurchase::STATUS_PAID)
            ->whereYear('paid_at', now()->year)
            ->selectRaw('MONTH(paid_at) as month_num, SUM(votes_count) as count')
            ->groupBy('month_num')
            ->pluck('count', 'month_num');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $spotlightPerformance = [];
        foreach ($months as $index => $monthName) {
            $monthNum = $index + 1;
            $free = $monthlyVotes->get($monthNum, 0);
            $paid = $monthlyPaidVotes->get($monthNum, 0);
            
            $spotlightPerformance[] = [
                'month' => $monthName,
                'value' => $free + $paid,
            ];
        }

        return $this->success('Artist dashboard stats retrieved successfully.', [
            'stats' => [
                'total_spotlight'    => $totalSpotlight,
                'approved_spotlight' => $approvedSpotlight,
                'ticket_purchasing'  => $ticketPurchasing,
            ],
            'spotlight_performance' => $spotlightPerformance,
        ]);
    }

    /**
     * GET /api/v1/artist/dashboard/analytics
     *
     * Returns analytics regarding spotlight reach and a 12-month metrics bar chart.
     */
    public function analytics(): JsonResponse
    {
        $userId = auth('api')->id();
        $artistSpotlightIds = ArtistSpotlight::where('user_id', $userId)->pluck('id');

        // 1. Spotlight Reach (Profile Visits / Spotlight View typically not explicitly tracked, defaulting securely)
        $profileVisits = 0;
        $spotlightView = 0;

        // 2. Votes Performance (12 Months chart for clap/like, share, save/bookmark)
        // Artist uses: artist_spotlight_likes, artist_spotlight_shares, artist_spotlight_bookmarks 
        $likes = DB::table('artist_spotlight_likes')
            ->whereIn('artist_spotlight_id', $artistSpotlightIds)
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month_num, COUNT(*) as count')
            ->groupBy('month_num')
            ->pluck('count', 'month_num');

        $shares = DB::table('artist_spotlight_shares')
            ->whereIn('artist_spotlight_id', $artistSpotlightIds)
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month_num, COUNT(*) as count')
            ->groupBy('month_num')
            ->pluck('count', 'month_num');

        $bookmarks = DB::table('artist_spotlight_bookmarks')
            ->whereIn('artist_spotlight_id', $artistSpotlightIds)
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month_num, COUNT(*) as count')
            ->groupBy('month_num')
            ->pluck('count', 'month_num');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $votesPerformance = [];
        foreach ($months as $index => $monthName) {
            $monthNum = $index + 1;

            $votesPerformance[] = [
                'month' => $monthName,
                'clap'  => $likes->get($monthNum, 0),
                'share' => $shares->get($monthNum, 0),
                'save'  => $bookmarks->get($monthNum, 0),
            ];
        }

        return $this->success('Artist analytics retrieved successfully.', [
            'spotlight_reach' => [
                'total_reach'    => $profileVisits + $spotlightView,
                'profile_visits' => $profileVisits,
                'spotlight_view' => $spotlightView,
            ],
            'spotlight_performance' => $votesPerformance,
        ]);
    }
}
