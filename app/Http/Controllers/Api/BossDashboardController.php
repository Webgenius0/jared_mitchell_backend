<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSpotlight;
use App\Models\EventRegistration;
use App\Models\Spotlight\SpotlightVote;
use App\Models\Spotlight\SpotlightVotePurchase;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Round;
use App\Models\Contest\Contestant;
use App\Models\Contest\Vote as ContestVote;
use App\Services\Contest\LeaderboardService;

class BossDashboardController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/boss/dashboard/stats
     *
     * Get overview statistics for the authenticated boss user's dashboard.
     *
     * Returns:
     *   - total_businesses: Number of businesses owned by this user
     *   - total_spotlights: Number of business spotlights owned by this user
     *   - total_votes: Total votes received across all of this user's spotlight nominees
     *   - total_event_purchases: Number of paid event registrations by this user
     */
    public function overview(): JsonResponse
    {
        $userId = auth('api')->id();

        // 1. Total businesses owned by this user
        $totalBusinesses = Business::where('user_id', $userId)->count();

        // 2. Total business spotlights (non-draft) owned by this user
        $totalSpotlights = BusinessSpotlight::where('user_id', $userId)
            ->where('status', '!=', 'draft')
            ->count();

        // 3. Total votes received across all spotlight nominees owned by this user
        $totalVotes = SpotlightVote::whereHas('nominee', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();

        // 4. Total event purchases (paid registrations) by this user
        $totalEventPurchases = EventRegistration::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->count();

        return $this->success('Business dashboard stats retrieved successfully.', [
            'total_businesses' => $totalBusinesses,
            'total_spotlights' => $totalSpotlights,
            'total_votes' => $totalVotes,
            'total_event_purchases' => $totalEventPurchases,
        ]);
    }

    /**
     * GET /api/v1/boss/dashboard/spotlight-performance
     *
     * Get the current spotlight week performance for the authenticated boss user's
     * nominated spotlights. Includes vote counts and daily vote trend data.
     *
     * @queryParam week_id int Optional. Specific week ID (defaults to current voting week).
     */
    public function spotlightPerformance(): JsonResponse
    {
        $userId = auth('api')->id();

        // Resolve the week: if a week_id query param is given, use that; else, find the current active week
        $weekId = request()->input('week_id');

        if ($weekId) {
            $week = SpotlightWeek::findOrFail($weekId);
        } else {
            // Try current voting week first, then fall back to most recent active week
            $week = SpotlightWeek::votingOpen()->latest('voting_starts_at')->first();

            if (!$week) {
                $week = SpotlightWeek::whereIn('status', ['nominating', 'voting', 'completed'])
                    ->latest('voting_starts_at')
                    ->first();
            }
        }

        if (!$week) {
            return $this->success('No spotlight week found.', [
                'week' => null,
                'nominees' => [],
                'vote_trend' => [],
            ]);
        }

        // Get this user's nominees for this week
        $nominees = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
            ->where('user_id', $userId)
            ->with(['spotlightable', 'week'])
            ->orderByDesc('total_vote_count')
            ->get();

        // Format nominees with vote details
        $nomineesData = $nominees->map(function ($nominee) {
            $spotlight = $nominee->spotlightable;

            return [
                'id' => $nominee->id,
                'rank' => $nominee->rank,
                'is_winner' => $nominee->is_winner,
                'free_vote_count' => $nominee->free_vote_count,
                'paid_vote_count' => $nominee->paid_vote_count,
                'total_vote_count' => $nominee->total_vote_count,
                'paid_vote_cap' => SpotlightWeek::maxPurchasedVotes(),
                'cap_reached' => $nominee->hasReachedPaidVoteCap(),
                'remaining_slots' => $nominee->remainingPaidVoteSlots(),
                'spotlight' => $spotlight ? [
                    'id' => $spotlight->id,
                    'name' => $spotlight->business_name ?? $spotlight->owner_founder_name ?? '—',
                    'category' => $spotlight->business_category ?? null,
                    'city' => $spotlight->city ?? null,
                    'state' => $spotlight->state ?? null,
                    'photo' => $spotlight->portrait_photo_path ?? null,
                    'status' => $spotlight->status ?? null,
                ] : null,
            ];
        });

        // Build aggregated daily vote trend (all nominees combined) for an overall line/area chart
        $aggregateTrend = $this->buildAggregateTrend($week, $userId);

        // Build per-nominee vote trends for multi-series chart (each nominee as its own line)
        $nomineeTrends = $this->buildNomineeTrends($week, $nominees);

        return $this->success('Spotlight performance retrieved successfully.', [
            'week' => [
                'id' => $week->id,
                'week_number' => $week->week_number,
                'year' => $week->year,
                'status' => $week->status,
                'is_voting_open' => $week->isVotingOpen(),
                'voting_starts_at' => $week->voting_starts_at,
                'voting_ends_at' => $week->voting_ends_at,
            ],
            'nominees' => $nomineesData,
            'nominees_count' => $nominees->count(),
            'vote_trend' => $aggregateTrend,
            'nominee_vote_trends' => $nomineeTrends,
            'max_paid_votes' => SpotlightWeek::maxPurchasedVotes(),
        ]);
    }

    /**
     * GET /api/v1/boss/dashboard/summary
     *
     * Get a combined summary of recent activities, spotlight performance, and voting summary.
     */
    public function summary(): JsonResponse
    {
        $userId = auth('api')->id();


        // 1. Recent Activity
        // Business Interactions
        $businessInteractions = \App\Models\BusinessInteraction::with('user.profile')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($interaction) {
                return [
                    'user_name' => $interaction->user->profile->name ?? $interaction->user->email ?? 'Unknown User',
                    'avatar' => $interaction->user->profile->avatar_url ?? null,
                    'activity' => 'Business ' . e($interaction->action_type),
                    'created_at' => $interaction->created_at,
                ];
            });

        // Spotlight Votes
        $spotlightVotes = \App\Models\Spotlight\SpotlightVote::with('user.profile')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($vote) {
                return [
                    'user_name' => $vote->user->profile->name ?? $vote->user->email ?? 'Unknown User',
                    'avatar' => $vote->user->profile->avatar_url ?? null,
                    'activity' => 'Voted for spotlight',
                    'created_at' => $vote->created_at,
                ];
            });

        // Profile Updates
        $profileUpdates = \App\Models\Profile::with('user')
            ->whereColumn('updated_at', '>', 'created_at')
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->map(function ($profile) {
                return [
                    'user_name' => $profile->name ?? $profile->user->email ?? 'Unknown User',
                    'avatar' => $profile->avatar_url,
                    'activity' => 'Updated profile',
                    'created_at' => $profile->updated_at,
                ];
            });

        $recentActivity = $businessInteractions->concat($spotlightVotes)->concat($profileUpdates)
            ->sortByDesc('created_at')
            ->take(15)
            ->values()
            ->toArray();

        // 2. Spotlight Performance
        $fullPerformanceData = $this->spotlightPerformance()->getData(true)['data'] ?? [];

        // Ensure week_base is not null
        $weekBase = $fullPerformanceData['week'] ?? [
            'id' => 0,
            'week_number' => 0,
            'year' => date('Y'),
            'status' => 'none',
            'is_voting_open' => false,
            'voting_starts_at' => null,
            'voting_ends_at' => null,
        ];

        // Ensure day_wise has exactly 7 days starting from Sunday
        // We will map any existing trend data to its day of the week name
        $trendData = collect($fullPerformanceData['vote_trend'] ?? [])
            ->mapWithKeys(function ($item) {
                if (isset($item['date'])) {
                    $dayName = \Carbon\Carbon::parse($item['date'])->format('l'); // Returns "Sunday", "Monday", etc.
                    return [$dayName => $item['total_vote_count'] ?? 0];
                }
                return [];
            });

        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $dayWise = [];
        foreach ($daysOfWeek as $dayName) {
            $dayWise[] = [
                'day' => $dayName,
                'value' => $trendData->get($dayName, 0),
            ];
        }

        // Add profile visits day wise (last 7 days)
        $businessesIds = Business::where('user_id', $userId)->pluck('id');
        $visitsByDate = \App\Models\BusinessInteraction::whereIn('business_id', $businessesIds)
            ->where('action_type', 'profile_visit')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as visit_count')
            ->groupBy('date')
            ->pluck('visit_count', 'date');

        $visitsTrendData = $visitsByDate->mapWithKeys(function ($count, $date) {
            $dayName = \Carbon\Carbon::parse($date)->format('l');
            return [$dayName => $count];
        });

        $profileVisitsDayWise = [];
        foreach ($daysOfWeek as $dayName) {
            $profileVisitsDayWise[] = [
                'day' => $dayName,
                'value' => $visitsTrendData->get($dayName, 0),
            ];
        }

        $performanceData = [
            'week_base' => $weekBase,
            'day_wise' => $dayWise, // vote trend day-wise
            'profile_visits_day_wise' => $profileVisitsDayWise,
        ];

        // 3. Voting Summary
        $businesses = Business::where('user_id', $userId)->get();
        $totalClap = $businesses->sum('total_claps');
        $totalShare = $businesses->sum('total_shares');

        $totalVote = SpotlightWeekNominee::where('user_id', $userId)
            ->sum('total_vote_count');

        // Get rank based on the active spotlight week shown in performance
        $rank = 0;
        if (!empty($weekBase['id'])) {
            $rank = SpotlightWeekNominee::where('user_id', $userId)
                ->where('spotlight_week_id', $weekBase['id'])
                ->min('rank');
        }

        // Fallback to absolute minimum rank if this week had no rank or no week given
        if (is_null($rank)) {
            $rank = SpotlightWeekNominee::where('user_id', $userId)->min('rank') ?? 0;
        }

        return $this->success('Boss dashboard summary retrieved successfully.', [
            'recent_activity' => $recentActivity,
            'spotlight_performance' => $performanceData,
            'voting_summary' => [
                'total_clap' => $totalClap,
                'total_vote' => $totalVote,
                'total_share' => $totalShare,
                'rank' => $rank,
            ]
        ]);
    }

    /**
     * GET /api/v1/boss/dashboard/analytics
     *
     * Get detailed analytics matching the UI dashboard widgets.
     */
    public function analytics(): JsonResponse
    {
        $userId = auth('api')->id();

        // 1. Votes
        $totalVote = SpotlightWeekNominee::where('user_id', $userId)->sum('total_vote_count');

        $nomineeIds = SpotlightWeekNominee::where('user_id', $userId)->pluck('id');

        $todaysVoteFree = SpotlightVote::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->whereDate('created_at', today())
            ->count();
        $todaysVotePaid = SpotlightVotePurchase::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->where('status', SpotlightVotePurchase::STATUS_PAID)
            ->whereDate('paid_at', today())
            ->sum('votes_count');
        $todaysVote = $todaysVoteFree + $todaysVotePaid;

        $weeklyVoteFree = SpotlightVote::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        $weeklyVotePaid = SpotlightVotePurchase::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->where('status', SpotlightVotePurchase::STATUS_PAID)
            ->whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('votes_count');
        $weeklyVote = $weeklyVoteFree + $weeklyVotePaid;

        $monthlyVoteFree = SpotlightVote::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $monthlyVotePaid = SpotlightVotePurchase::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->where('status', SpotlightVotePurchase::STATUS_PAID)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('votes_count');
        $monthlyVote = $monthlyVoteFree + $monthlyVotePaid;

        // 2. Spotlight Reach
        $businessIds = Business::where('user_id', $userId)->pluck('id');
        $profileVisits = \App\Models\BusinessInteraction::whereIn('business_id', $businessIds)
            ->where('action_type', 'profile_visit')
            ->count();
        $spotlightView = 0; // Not explicitly recorded natively in system yet

        // 3. Votes Performance (12 Months chart for clap, share, save)
        $performanceInteractions = \App\Models\BusinessInteraction::whereIn('business_id', $businessIds)
            ->whereIn('action_type', ['clap', 'share', 'save'])
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month_num, action_type, COUNT(*) as count')
            ->groupBy('month_num', 'action_type')
            ->get();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $votesPerformance = [];
        foreach ($months as $index => $monthName) {
            $monthNum = $index + 1;

            $clap = $performanceInteractions->where('month_num', $monthNum)->where('action_type', 'clap')->sum('count');
            $share = $performanceInteractions->where('month_num', $monthNum)->where('action_type', 'share')->sum('count');
            $save = $performanceInteractions->where('month_num', $monthNum)->where('action_type', 'save')->sum('count');

            $votesPerformance[] = [
                'month' => $monthName,
                'clap' => $clap,
                'share' => $share,
                'save' => $save,
            ];
        }

        return $this->success('Analytics retrieved successfully.', [
            'votes' => [
                'total_vote' => $totalVote,
                'todays_vote' => $todaysVote,
                'weekly_vote' => $weeklyVote,
                'monthly_vote' => $monthlyVote,
            ],
            'spotlight_reach' => [
                'total_reach' => $profileVisits + $spotlightView,
                'profile_visits' => $profileVisits,
                'spotlight_view' => $spotlightView,
            ],
            'votes_performance' => $votesPerformance,
            'engagement_rate' => [
                'spotlight_view' => $spotlightView,
                'profile_visits' => $profileVisits,
                'total_vote' => $totalVote,
            ]
        ]);
    }

    /**
     * GET /api/v1/boss/dashboard/contest-summary
     *
     * Get overall contest summary, year-wise monthly summary, and round-wise data.
     */
    public function contestSummary(LeaderboardService $leaderboardService): JsonResponse
    {
        $userId = auth('api')->id();

        // 1. Find user's businesses and contestants
        $businessIds = Business::where('user_id', $userId)->pluck('id');
        $contestants = Contestant::where('contestable_type', Business::class)
            ->whereIn('contestable_id', $businessIds)
            ->get();
        $contestantIds = $contestants->pluck('id');

        // ==== OVERALL SUMMARY ====
        $allVotesQuery = ContestVote::where('votable_type', Contestant::class)
            ->whereIn('votable_id', $contestantIds);

        $totalVotes = $allVotesQuery->count();
        $todayVotes = (clone $allVotesQuery)->whereDate('created_at', today())->count();
        $weekVotes = (clone $allVotesQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $monthVotes = (clone $allVotesQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Year-based monthly summary
        $year = request('year', now()->year);
        $interactions = \App\Models\BusinessInteraction::whereIn('business_id', $businessIds)
            ->whereIn('action_type', ['clap', 'share', 'save', 'fire'])
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month_num, action_type, COUNT(*) as count')
            ->groupBy('month_num', 'action_type')
            ->get();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlySummary = [];
        foreach ($months as $index => $monthName) {
            $monthNum = $index + 1;
            $monthlySummary[] = [
                'month' => $monthName,
                'clap' => (int) $interactions->where('month_num', $monthNum)->where('action_type', 'clap')->sum('count'),
                'share' => (int) $interactions->where('month_num', $monthNum)->where('action_type', 'share')->sum('count'),
                'fire' => (int) $interactions->where('month_num', $monthNum)->whereIn('action_type', ['save', 'fire'])->sum('count'), // Mapping to fire/save
            ];
        }

        // ==== ROUND-WISE SUMMARY ====
        $rounds = Round::orderBy('round_number')->take(5)->get();
        $roundWiseSummary = [];

        foreach ($rounds as $round) {
            $roundVotesQuery = ContestVote::where('votable_type', Contestant::class)
                ->whereIn('votable_id', $contestantIds)
                ->where('round_id', $round->id);

            if ($round->round_number === 1) {
                // Find rank
                $leaderboard = $leaderboardService->getLeaderboard($round);
                $rank = 0;
                foreach ($leaderboard as $entry) {
                    if ($contestantIds->contains($entry['contestant_id'])) {
                        // Lowest rank (best position) if multiple contestants
                        if ($rank === 0 || $entry['rank'] < $rank) {
                            $rank = $entry['rank'];
                        }
                    }
                }

                $totalClap = Business::whereIn('id', $businessIds)->sum('total_claps');
                $totalSave = Business::whereIn('id', $businessIds)->sum('total_saves');
                $totalFire = \App\Models\BusinessInteraction::whereIn('business_id', $businessIds)->where('action_type', 'fire')->count();

                $roundWiseSummary[] = [
                    'round' => 'Round 1',
                    'total_votes' => $roundVotesQuery->count(),
                    'todays_votes' => (clone $roundVotesQuery)->whereDate('created_at', today())->count(),
                    'weekly_votes' => (clone $roundVotesQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'monthly_votes' => (clone $roundVotesQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                    'voting_summary' => [
                        'total_clap' => (int) $totalClap,
                        'total_save' => (int) $totalSave,
                        'total_fire' => (int) $totalFire, // Keep original values as requested
                        'rank' => $rank,
                    ],
                ];
            } else {
                $roundWiseSummary[] = [
                    'round' => 'Round ' . $round->round_number,
                    'total_points' => (float) (clone $roundVotesQuery)->sum('weight'),
                    'todays_points' => (float) (clone $roundVotesQuery)->whereDate('created_at', today())->sum('weight'),
                    'weekly_points' => (float) (clone $roundVotesQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('weight'),
                    'monthly_points' => (float) (clone $roundVotesQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('weight'),
                ];
            }
        }

        return $this->success('Contest summary retrieved successfully.', [
            'overall_summary' => [
                'total_votes' => $totalVotes,
                'todays_votes' => $todayVotes,
                'this_weeks_votes' => $weekVotes,
                'this_months_votes' => $monthVotes,
            ],
            'year_based_monthly_summary' => $monthlySummary,
            'round_wise_summary' => $roundWiseSummary,
        ]);
    }

    /**
     * Build aggregated daily vote trend across ALL of this user's nominees in the week.
     *
     * Returns daily breakdown of free votes (SpotlightVote) and paid votes
     * (SpotlightVotePurchase where status='paid'), plus cumulative totals.
     * Perfect for an overall line or stacked area chart.
     *
     * @return array<int, array{date: string, free_vote_count: int, paid_vote_count: int, total_vote_count: int, cumulative_free: int, cumulative_paid: int, cumulative_total: int}>
     */
    private function buildAggregateTrend(SpotlightWeek $week, int $userId): array
    {
        $nomineeIds = $this->getUserNomineeIds($week->id, $userId);

        if ($nomineeIds->isEmpty() || !$week->voting_starts_at) {
            return [];
        }

        $startDate = $week->voting_starts_at->copy()->startOfDay();
        $endDate = now()->min($week->voting_ends_at ?? now())->endOfDay();

        // 1. Daily free vote counts (from SpotlightVote.created_at)
        $freeVotes = SpotlightVote::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as vote_count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('vote_count', 'date')
            ->toArray();

        // 2. Daily paid vote counts (from SpotlightVotePurchase.paid_at where status='paid')
        $paidVotes = SpotlightVotePurchase::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->where('status', SpotlightVotePurchase::STATUS_PAID)
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $startDate)
            ->where('paid_at', '<=', $endDate)
            ->selectRaw('DATE(paid_at) as date')
            ->selectRaw('SUM(votes_count) as vote_count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('vote_count', 'date')
            ->toArray();

        // 3. Fill in all days with both free and paid data
        $trend = [];
        $cumulativeFree = 0;
        $cumulativePaid = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dateKey = $current->format('Y-m-d');

            $dayFree = (int) ($freeVotes[$dateKey] ?? 0);
            $dayPaid = (int) ($paidVotes[$dateKey] ?? 0);
            $dayTotal = $dayFree + $dayPaid;

            $cumulativeFree += $dayFree;
            $cumulativePaid += $dayPaid;

            $trend[] = [
                'date' => $dateKey,
                'free_vote_count' => $dayFree,
                'paid_vote_count' => $dayPaid,
                'total_vote_count' => $dayTotal,
                'cumulative_free' => $cumulativeFree,
                'cumulative_paid' => $cumulativePaid,
                'cumulative_total' => $cumulativeFree + $cumulativePaid,
            ];

            $current->addDay();
        }

        return $trend;
    }

    /**
     * Build per-nominee daily vote trends so the frontend can render
     * multi-series charts (e.g. one line per nominee).
     *
     * @return array<int, array{nominee_id: int, name: string, data: array}>
     */
    private function buildNomineeTrends(SpotlightWeek $week, $nominees): array
    {
        $nomineeIds = $nominees->pluck('id');

        if ($nomineeIds->isEmpty() || !$week->voting_starts_at) {
            return [];
        }

        $startDate = $week->voting_starts_at->copy()->startOfDay();
        $endDate = now()->min($week->voting_ends_at ?? now())->endOfDay();

        // Get all free votes grouped by nominee + date
        $freeVotesByNominee = SpotlightVote::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->selectRaw('spotlight_week_nominee_id')
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as vote_count')
            ->groupBy('spotlight_week_nominee_id', 'date')
            ->orderBy('date')
            ->get()
            ->groupBy('spotlight_week_nominee_id')
            ->map(function ($rows) {
                return $rows->pluck('vote_count', 'date')->toArray();
            })
            ->toArray();

        // Get paid votes by nominee + date
        $paidVotesByNominee = SpotlightVotePurchase::whereIn('spotlight_week_nominee_id', $nomineeIds)
            ->where('status', SpotlightVotePurchase::STATUS_PAID)
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $startDate)
            ->where('paid_at', '<=', $endDate)
            ->selectRaw('spotlight_week_nominee_id')
            ->selectRaw('DATE(paid_at) as date')
            ->selectRaw('SUM(votes_count) as vote_count')
            ->groupBy('spotlight_week_nominee_id', 'date')
            ->orderBy('date')
            ->get()
            ->groupBy('spotlight_week_nominee_id')
            ->map(function ($rows) {
                return $rows->pluck('vote_count', 'date')->toArray();
            })
            ->toArray();

        $trends = [];

        foreach ($nominees as $nominee) {
            $nomineeId = $nominee->id;
            $spotlight = $nominee->spotlightable;
            $name = $spotlight
                ? ($spotlight->business_name ?? $spotlight->owner_founder_name ?? '—')
                : '—';

            $freeMap = $freeVotesByNominee[$nomineeId] ?? [];
            $paidMap = $paidVotesByNominee[$nomineeId] ?? [];

            $data = [];
            $cumulativeFree = 0;
            $cumulativePaid = 0;
            $current = $startDate->copy();

            while ($current->lte($endDate)) {
                $dateKey = $current->format('Y-m-d');

                $dayFree = (int) ($freeMap[$dateKey] ?? 0);
                $dayPaid = (int) ($paidMap[$dateKey] ?? 0);

                $cumulativeFree += $dayFree;
                $cumulativePaid += $dayPaid;

                $data[] = [
                    'date' => $dateKey,
                    'free_vote_count' => $dayFree,
                    'paid_vote_count' => $dayPaid,
                    'total_vote_count' => $dayFree + $dayPaid,
                    'cumulative_free' => $cumulativeFree,
                    'cumulative_paid' => $cumulativePaid,
                    'cumulative_total' => $cumulativeFree + $cumulativePaid,
                ];

                $current->addDay();
            }

            $trends[] = [
                'nominee_id' => $nomineeId,
                'name' => $name,
                'spotlight_id' => $spotlight?->id,
                'total_votes' => $nominee->total_vote_count,
                'data' => $data,
            ];
        }

        return $trends;
    }

    /**
     * Helper: get nominee IDs for this user in this week.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getUserNomineeIds(int $weekId, int $userId)
    {
        return SpotlightWeekNominee::where('spotlight_week_id', $weekId)
            ->where('user_id', $userId)
            ->pluck('id');
    }
}
