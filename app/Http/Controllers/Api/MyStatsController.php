<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contest\Vote as ContestVote;
use App\Models\EventRegistration;
use App\Models\Profile;
use App\Models\Spotlight\SpotlightVote;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class MyStatsController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/my/stats
     *
     * Get the authenticated user's activity summary:
     *   - total_votes_given : votes given to any businesses or artists (contest + spotlight)
     *   - total_bossbegging : votes given in the Boss Beginnings contest
     *   - total_spotlight   : votes given in spotlight voting
     *   - total_tickets     : purchased event tickets
     *   - recent_activities : my own activities — votes I cast and my profile updates
     */
    public function stats(): JsonResponse
    {
        $userId = auth('api')->id();

        // 1. Boss Beginnings contest votes given by this user
        $totalBossbegging = ContestVote::where('user_id', $userId)->count();

        // 2. Spotlight votes given by this user (free community votes)
        $totalSpotlight = SpotlightVote::where('user_id', $userId)->count();

        // 3. Total votes given to any businesses or artists (contest + spotlight)
        $totalVotesGiven = $totalBossbegging + $totalSpotlight;

        // 4. Purchased event tickets (paid registrations)
        $totalTickets = EventRegistration::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->count();

        // 5. My own activities — votes I cast and my profile updates only
        $recentActivities = $this->myActivities($userId);

        // 6. My basic info (name, email, photo)
        $user = auth('api')->user()->load('profile');

        return $this->success('User stats retrieved successfully.', [

            'total_votes_given' => $totalVotesGiven,
            'total_bossbegging' => $totalBossbegging,
            'total_spotlight' => $totalSpotlight,
            'total_tickets' => $totalTickets,
            'user' => [
                'name' => $user->profile?->name ?? '',
                'email' => $user->email ?? '',
                'photo' => $user->profile?->avatar_url ?? asset('admin/default/user.jpg'),
            ],
            'recent_activities' => $recentActivities,
        ]);
    }

    /**
     * Build the authenticated user's own activities only:
     * votes I cast (contest + spotlight) and my profile updates.
     * No actions performed by other users and no other-entity info are included.
     *
     * @return array<int, array{activity: string, day: string, created_at: string}>
     */
    private function myActivities(int $userId): array
    {
        // Contest votes (Boss Beginnings) I cast
        $contestActivities = ContestVote::where('user_id', $userId)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($vote) {
                return [
                    'activity' => 'Voted in Boss Beginnings',
                    'day' => $vote->created_at?->format('l, d M Y'),
                    'created_at' => $vote->created_at?->toISOString(),
                ];
            });

        // Spotlight votes I cast
        $spotlightActivities = SpotlightVote::where('user_id', $userId)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($vote) {
                return [
                    'activity' => 'Voted in Spotlight',
                    'day' => $vote->created_at?->format('l, d M Y'),
                    'created_at' => $vote->created_at?->toISOString(),
                ];
            });

        // My profile updates
        $profileActivities = Profile::where('user_id', $userId)
            ->whereColumn('updated_at', '>', 'created_at')
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->map(function ($profile) {
                return [
                    'activity' => 'Updated profile',
                    'day' => $profile->updated_at?->format('l, d M Y'),
                    'created_at' => $profile->updated_at?->toISOString(),
                ];
            });

        return $contestActivities
            ->concat($spotlightActivities)
            ->concat($profileActivities)
            ->sortByDesc('created_at')
            ->take(15)
            ->values()
            ->toArray();
    }
}
