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
            'total_businesses'      => $totalBusinesses,
            'total_spotlights'      => $totalSpotlights,
            'total_votes'           => $totalVotes,
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

            if (! $week) {
                $week = SpotlightWeek::whereIn('status', ['nominating', 'voting', 'completed'])
                    ->latest('voting_starts_at')
                    ->first();
            }
        }

        if (! $week) {
            return $this->success('No spotlight week found.', [
                'week'       => null,
                'nominees'   => [],
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
                'id'               => $nominee->id,
                'rank'             => $nominee->rank,
                'is_winner'        => $nominee->is_winner,
                'free_vote_count'  => $nominee->free_vote_count,
                'paid_vote_count'  => $nominee->paid_vote_count,
                'total_vote_count' => $nominee->total_vote_count,
                'paid_vote_cap'    => SpotlightWeek::maxPurchasedVotes(),
                'cap_reached'      => $nominee->hasReachedPaidVoteCap(),
                'remaining_slots'  => $nominee->remainingPaidVoteSlots(),
                'spotlight'        => $spotlight ? [
                    'id'           => $spotlight->id,
                    'name'         => $spotlight->business_name ?? $spotlight->owner_founder_name ?? '—',
                    'category'     => $spotlight->business_category ?? null,
                    'city'         => $spotlight->city ?? null,
                    'state'        => $spotlight->state ?? null,
                    'photo'        => $spotlight->portrait_photo_path ?? null,
                    'status'       => $spotlight->status ?? null,
                ] : null,
            ];
        });

        // Build aggregated daily vote trend (all nominees combined) for an overall line/area chart
        $aggregateTrend = $this->buildAggregateTrend($week, $userId);

        // Build per-nominee vote trends for multi-series chart (each nominee as its own line)
        $nomineeTrends = $this->buildNomineeTrends($week, $nominees);

        return $this->success('Spotlight performance retrieved successfully.', [
            'week' => [
                'id'               => $week->id,
                'week_number'      => $week->week_number,
                'year'             => $week->year,
                'status'           => $week->status,
                'is_voting_open'   => $week->isVotingOpen(),
                'voting_starts_at' => $week->voting_starts_at,
                'voting_ends_at'   => $week->voting_ends_at,
            ],
            'nominees'               => $nomineesData,
            'nominees_count'         => $nominees->count(),
            'vote_trend'             => $aggregateTrend,
            'nominee_vote_trends'    => $nomineeTrends,
            'max_paid_votes'         => SpotlightWeek::maxPurchasedVotes(),
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

        if ($nomineeIds->isEmpty() || ! $week->voting_starts_at) {
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
                'date'             => $dateKey,
                'free_vote_count'  => $dayFree,
                'paid_vote_count'  => $dayPaid,
                'total_vote_count' => $dayTotal,
                'cumulative_free'  => $cumulativeFree,
                'cumulative_paid'  => $cumulativePaid,
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

        if ($nomineeIds->isEmpty() || ! $week->voting_starts_at) {
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
                    'date'             => $dateKey,
                    'free_vote_count'  => $dayFree,
                    'paid_vote_count'  => $dayPaid,
                    'total_vote_count' => $dayFree + $dayPaid,
                    'cumulative_free'  => $cumulativeFree,
                    'cumulative_paid'  => $cumulativePaid,
                    'cumulative_total' => $cumulativeFree + $cumulativePaid,
                ];

                $current->addDay();
            }

            $trends[] = [
                'nominee_id'    => $nomineeId,
                'name'          => $name,
                'spotlight_id'  => $spotlight?->id,
                'total_votes'   => $nominee->total_vote_count,
                'data'          => $data,
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
