<?php

namespace Database\Seeders;

use App\Models\ArtistSpotlight;
use App\Models\BusinessSpotlight;
use App\Models\Spotlight\SpotlightApplication;
use App\Models\Spotlight\SpotlightVote;
use App\Models\Spotlight\SpotlightVotePackage;
use App\Models\Spotlight\SpotlightVotePurchase;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SpotlightSeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates a full spotlight season for testing:
     * - 3 weeks (past completed, current voting, upcoming pending)
     * - Applications linking spotlights to weeks
     * - Nominees with vote counts for leaderboard
     * - Free community votes
     * - Vote purchases with various statuses
     * - A declared winner for the completed week
     */
    public function run(): void
    {
        // ── Guard: We need existing data to link against ──────────────
        $artistSpotlights = ArtistSpotlight::whereIn('status', ['approved', 'submitted', 'under_review'])->get();
        $businessSpotlights = BusinessSpotlight::whereIn('status', ['approved', 'submitted', 'under_review'])->get();

        if ($artistSpotlights->isEmpty() || $businessSpotlights->isEmpty()) {
            $this->command->warn('SpotlightSeasonSeeder: No approved/submitted spotlights found. Creating fallback spotlights...');

            // Fall back to any spotlights
            $artistSpotlights = ArtistSpotlight::all();
            $businessSpotlights = BusinessSpotlight::all();

            if ($artistSpotlights->isEmpty() || $businessSpotlights->isEmpty()) {
                $this->command->error('SpotlightSeasonSeeder: No ArtistSpotlight or BusinessSpotlight records exist. Run ArtistSpotlightSeeder + BusinessSpotlightSeeder first.');
                return;
            }

            // Update their statuses if needed so they can apply
            ArtistSpotlight::where('status', 'draft')->update(['status' => 'approved']);
            BusinessSpotlight::where('status', 'draft')->update(['status' => 'approved']);

            $artistSpotlights = ArtistSpotlight::all();
            $businessSpotlights = BusinessSpotlight::all();
        }

        $allUsers = User::all();
        $votePackages = SpotlightVotePackage::all();

        if ($votePackages->isEmpty()) {
            $this->command->warn('SpotlightSeasonSeeder: No vote packages found. Running SpotlightVotePackageSeeder...');
            $this->call(SpotlightVotePackageSeeder::class);
            $votePackages = SpotlightVotePackage::all();
        }

        $this->command->info('Found ' . $artistSpotlights->count() . ' artist spotlights and ' . $businessSpotlights->count() . ' business spotlights.');
        $this->command->info('Found ' . $allUsers->count() . ' users and ' . $votePackages->count() . ' vote packages.');

        // ── 1. Create Weeks ──────────────────────────────────────────
        $this->command->info('Creating spotlight weeks...');

        // Week 1: Completed (2 weeks ago, with winner)
        $week1Start = Carbon::now()->subWeeks(2)->startOfWeek()->addDay(); // Monday 2 weeks ago
        $week1VotingEnd = (clone $week1Start)->addDays(6)->endOfDay();    // Sunday

        $week1 = SpotlightWeek::firstOrCreate(
            ['week_number' => $week1Start->isoWeek(), 'year' => $week1Start->year],
            [
                'status'            => 'completed',
                'voting_starts_at'  => $week1Start,
                'voting_ends_at'    => $week1VotingEnd,
                'announced_at'      => (clone $week1VotingEnd)->addHours(2),
                'metadata'          => ['note' => 'Season 1 — Week 1 (completed)'],
            ]
        );

        // Week 2: Current — voting open (this week)
        $week2 = Carbon::now()->startOfWeek()->addDay(); // Monday
        $week2VotingEnd = (clone $week2)->addDays(6)->endOfDay(); // Sunday

        $week2 = SpotlightWeek::firstOrCreate(
            ['week_number' => $week2->isoWeek(), 'year' => $week2->year],
            [
                'status'            => 'voting',
                'voting_starts_at'  => $week2,
                'voting_ends_at'    => $week2VotingEnd,
                'metadata'          => ['note' => 'Season 1 — Week 2 (voting open)'],
            ]
        );

        // Week 3: Upcoming — pending (next week)
        $week3Start = Carbon::now()->addWeek()->startOfWeek()->addDay(); // Next Monday
        $week3VotingEnd = (clone $week3Start)->addDays(6)->endOfDay();

        $week3 = SpotlightWeek::firstOrCreate(
            ['week_number' => $week3Start->isoWeek(), 'year' => $week3Start->year],
            [
                'status'            => 'pending',
                'voting_starts_at'  => $week3Start,
                'voting_ends_at'    => $week3VotingEnd,
                'metadata'          => ['note' => 'Season 1 — Week 3 (upcoming)'],
            ]
        );

        $weeks = [$week1, $week2, $week3];

        // ── 2. Create Applications for all weeks ─────────────────────
        $this->command->info('Creating applications...');

        foreach ($weeks as $week) {
            // Pick random artist and business spotlights (not all, some variety)
            $artistCount = min(8, $artistSpotlights->count());
            $businessCount = min(8, $businessSpotlights->count());

            $selectedArtists = $artistSpotlights->random($artistCount);
            $selectedBusinesses = $businessSpotlights->random($businessCount);

            foreach ($selectedArtists as $spotlight) {
                $this->createApplication($week, $spotlight, ArtistSpotlight::class);
            }

            foreach ($selectedBusinesses as $spotlight) {
                $this->createApplication($week, $spotlight, BusinessSpotlight::class);
            }
        }

        // ── 3. Create Nominees for Week 1 & 2 (voting/completed weeks) ──
        $this->command->info('Creating nominees...');

        foreach ([$week1, $week2] as $week) {
            $applications = SpotlightApplication::where('spotlight_week_id', $week->id)
                ->where('status', 'selected')
                ->get();

            if ($applications->count() < 6) {
                // Not enough selected applications — select some pending ones
                $pendingApps = SpotlightApplication::where('spotlight_week_id', $week->id)
                    ->where('status', 'pending')
                    ->get();

                if ($pendingApps->count() > 0) {
                    foreach ($pendingApps->take(12 - $applications->count()) as $app) {
                        $app->update(['status' => 'selected', 'reviewed_at' => now()]);
                    }
                    $applications = SpotlightApplication::where('spotlight_week_id', $week->id)
                        ->where('status', 'selected')
                        ->get();
                }
            }

            // Take up to 12 nominees
            $nomineeApplications = $applications->take(12);

            foreach ($nomineeApplications as $app) {
                $freeVotes = $week->id === $week2->id ? rand(5, 45) : rand(10, 80);
                $paidVotes = $week->id === $week2->id ? rand(0, 20) : rand(0, 40);
                $totalVotes = $freeVotes + $paidVotes;

                SpotlightWeekNominee::firstOrCreate(
                    [
                        'spotlight_week_id'  => $week->id,
                        'spotlightable_type' => $app->spotlightable_type,
                        'spotlightable_id'   => $app->spotlightable_id,
                    ],
                    [
                        'user_id'         => $app->user_id,
                        'free_vote_count' => $freeVotes,
                        'paid_vote_count' => $paidVotes,
                        'total_vote_count' => $totalVotes,
                        'rank'            => null, // Will be set when voting closes
                        'is_winner'       => false,
                    ]
                );
            }
        }

        // ── 4. Set ranks and declare winner for Week 1 (completed) ──
        $this->command->info('Setting winner for completed week...');

        $week1Nominees = SpotlightWeekNominee::where('spotlight_week_id', $week1->id)
            ->orderByDesc('total_vote_count')
            ->get();

        if ($week1Nominees->count() > 0) {
            foreach ($week1Nominees as $index => $nominee) {
                $nominee->update([
                    'rank'      => $index + 1,
                    'is_winner' => $index === 0,
                ]);
            }

            // Set winner on the week
            $winner = $week1Nominees->first();
            $week1->update([
                'winner_spotlightable_type' => $winner->spotlightable_type,
                'winner_spotlightable_id'   => $winner->spotlightable_id,
                'announced_at'             => (clone $week1->voting_ends_at)->addHours(2),
            ]);

            $this->command->info("Week 1 winner: {$winner->spotlightable_type} #{$winner->spotlightable_id} with {$winner->total_vote_count} votes");
        }

        // ── 5. Create Free Votes for Week 2 (current voting week) ───
        $this->command->info('Creating free community votes...');

        $week2Nominees = SpotlightWeekNominee::where('spotlight_week_id', $week2->id)->get();

        if ($week2Nominees->count() > 0 && $allUsers->count() > 0) {
            // Each of ~15 users votes for 1-3 random nominees
            $votingUsers = $allUsers->random(min(15, $allUsers->count()));

            foreach ($votingUsers as $user) {
                $nomineesToVoteFor = $week2Nominees->random(min(3, $week2Nominees->count()));

                foreach ($nomineesToVoteFor as $nominee) {
                    try {
                        SpotlightVote::firstOrCreate(
                            [
                                'spotlight_week_nominee_id' => $nominee->id,
                                'user_id'                  => $user->id,
                            ],
                            [
                                'created_at' => now()->subHours(rand(1, 48)),
                                'updated_at' => now()->subHours(rand(1, 48)),
                            ]
                        );
                    } catch (\Exception $e) {
                        // Skip duplicate votes silently
                    }
                }
            }

            // Recalculate actual free vote counts to match created votes
            foreach ($week2Nominees as $nominee) {
                $actualFreeVotes = SpotlightVote::where('spotlight_week_nominee_id', $nominee->id)->count();
                $nominee->update([
                    'free_vote_count'  => $actualFreeVotes,
                    'total_vote_count' => $actualFreeVotes + $nominee->paid_vote_count,
                ]);
            }
        }

        // ── 6. Create Vote Purchases for Week 2 ─────────────────────
        $this->command->info('Creating vote purchases...');

        if ($week2Nominees->count() > 0 && $votePackages->count() > 0) {
            $purchaseCandidates = $week2Nominees->random(min(5, $week2Nominees->count()));

            foreach ($purchaseCandidates as $index => $nominee) {
                $package = $votePackages->random();
                $adminUser = User::role('super-admin')->first() ?? User::role('admin')->first() ?? $allUsers->first();
                $statuses = ['pending', 'approved', 'paid', 'paid', 'refunded', 'cancelled'];
                $status = $statuses[$index % count($statuses)];

                $purchaseData = [
                    'spotlight_week_nominee_id' => $nominee->id,
                    'user_id'                   => $nominee->user_id,
                    'spotlight_vote_package_id' => $package->id,
                    'package'                   => $package->slug,
                    'votes_count'               => $package->votes_count,
                    'amount_paid'               => $package->price,
                    'status'                    => $status,
                ];

                if (in_array($status, ['paid', 'refunded'])) {
                    $purchaseData['approved_by'] = $adminUser->id;
                    $purchaseData['approved_at'] = now()->subDays(2);
                    $purchaseData['stripe_checkout_session_id'] = 'cs_test_' . \Illuminate\Support\Str::random(24);
                    $purchaseData['stripe_payment_intent_id'] = 'pi_test_' . \Illuminate\Support\Str::random(24);

                    if ($status === 'paid') {
                        $purchaseData['paid_at'] = now()->subDay();
                        // Update the nominee's paid vote count
                        $nominee->addPaidVotes($package->votes_count);
                    }
                } elseif ($status === 'approved') {
                    $purchaseData['approved_by'] = $adminUser->id;
                    $purchaseData['approved_at'] = now()->subDay();
                }

                $purchase = SpotlightVotePurchase::create($purchaseData);

                $this->command->info("  Created {$status} purchase #{$purchase->id}: {$package->name} for nominee #{$nominee->id}");
            }
        }

        // ── 7. Update Week 2 leaderboard after purchase adjustments ──
        // Re-fetch fresh data from DB to reflect any mutations from the purchase section above
        $week2Nominees = SpotlightWeekNominee::where('spotlight_week_id', $week2->id)->get();
        foreach ($week2Nominees as $nominee) {
            $freeVotes = SpotlightVote::where('spotlight_week_nominee_id', $nominee->id)->count();
            $paidVotes = SpotlightVotePurchase::where('spotlight_week_nominee_id', $nominee->id)
                ->where('status', 'paid')
                ->sum('votes_count');

            $nominee->update([
                'free_vote_count'  => $freeVotes,
                'paid_vote_count'  => $paidVotes,
                'total_vote_count' => $freeVotes + $paidVotes,
            ]);
        }

        $this->command->info('');
        $this->command->info('✅ Spotlight Season Seeding Complete!');
        $this->command->info('  • ' . count($weeks) . ' weeks created');
        $this->command->info('  • Applications created for all weeks');
        $this->command->info("  • {$week1Nominees->count()} nominees for Week 1 (completed) — winner declared");
        $this->command->info("  • {$week2Nominees->count()} nominees for Week 2 (voting open) — with votes & purchases");
        $this->command->info('  • 1 upcoming week (pending)');
    }

    /**
     * Create a spotlight application for a given week and spotlight.
     */
    private function createApplication(SpotlightWeek $week, $spotlight, string $spotlightType): void
    {
        // Determine if this application is selected or pending
        $isVotingOrCompleted = in_array($week->status, ['voting', 'completed']);
        $status = $isVotingOrCompleted
            ? (rand(0, 3) === 0 ? 'rejected' : 'selected') // 75% selected for voting weeks
            : 'pending';

        $userId = $spotlight->user_id ?? User::inRandomOrder()->first()?->id;

        if (! $userId) {
            return;
        }

        $appliedAt = $week->voting_starts_at?->copy()->subDays(rand(1, 5));

        try {
            SpotlightApplication::firstOrCreate(
                [
                    'spotlight_week_id' => $week->id,
                    'spotlightable_type' => $spotlightType,
                    'spotlightable_id'  => $spotlight->id,
                ],
                [
                    'user_id'     => $userId,
                    'status'      => $status,
                    'applied_at'  => $appliedAt ?? now(),
                    'reviewed_at' => $status !== 'pending' ? now() : null,
                ]
            );
        } catch (\Exception $e) {
            // Skip duplicates silently
        }
    }
}
