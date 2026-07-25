<?php

namespace Database\Seeders;

use App\Models\ArtistSpotlight;
use App\Models\BusinessSpotlight;
use App\Models\Spotlight\SpotlightApplication;
use App\Models\Spotlight\SpotlightWeek;
use App\Models\Spotlight\SpotlightWeekNominee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SpotlightHistoricalWinnersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds completed spotlight weeks with winners of both artist and business types
     * spanning the last 6 months, so the /api/v1/spotlight/historical-winners endpoint
     * returns data for both ?type=artist and ?type=business.
     */
    public function run(): void
    {
        $artistSpotlights = ArtistSpotlight::whereIn('status', ['approved', 'submitted', 'under_review'])->get();
        $businessSpotlights = BusinessSpotlight::whereIn('status', ['approved', 'submitted', 'under_review'])->get();

        if ($artistSpotlights->isEmpty()) {
            $artistSpotlights = ArtistSpotlight::all();
            if ($artistSpotlights->isEmpty()) {
                $this->command->error('SpotlightHistoricalWinnersSeeder: No ArtistSpotlight records exist. Run ArtistSpotlightSeeder first.');
                return;
            }
            ArtistSpotlight::where('status', 'draft')->update(['status' => 'approved']);
            $artistSpotlights = ArtistSpotlight::all();
        }

        if ($businessSpotlights->isEmpty()) {
            $businessSpotlights = BusinessSpotlight::all();
            if ($businessSpotlights->isEmpty()) {
                $this->command->error('SpotlightHistoricalWinnersSeeder: No BusinessSpotlight records exist. Run BusinessSpotlightSeeder first.');
                return;
            }
            BusinessSpotlight::where('status', 'draft')->update(['status' => 'approved']);
            $businessSpotlights = BusinessSpotlight::all();
        }

        $allUsers = User::all();
        if ($allUsers->isEmpty()) {
            $this->command->error('SpotlightHistoricalWinnersSeeder: No users found. Run UserSeeder first.');
            return;
        }

        $this->command->info('Found ' . $artistSpotlights->count() . ' artist spotlights, ' . $businessSpotlights->count() . ' business spotlights, and ' . $allUsers->count() . ' users.');

        // ── Create 5 completed weeks spanning months 5 through 1 ago ──
        // We skip the current month (monthsAgo = 0) to avoid accidentally
        // overwriting a live voting week that SpotlightSeasonSeeder created.
        $weeksCreated = 0;
        $artistWinnerCount = 0;
        $businessWinnerCount = 0;

        for ($monthsAgo = 5; $monthsAgo >= 1; $monthsAgo--) {
            $monthStart = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            // Find the Monday on or after the 1st of the month
            $weekStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
            if ($weekStart->lt($monthStart)) {
                $weekStart->addWeek();
            }

            $votingEnd = (clone $weekStart)->addDays(6)->endOfDay();
            $weekNumber = $weekStart->isoWeek();
            $year = $weekStart->year;

            // Skip if this week already exists and is not 'pending' (i.e. already active)
            $existingWeek = SpotlightWeek::where('week_number', $weekNumber)
                ->where('year', $year)
                ->first();

            if ($existingWeek) {
                // Don't touch voting weeks — they're still live
                if (in_array($existingWeek->status, ['voting', 'pending', 'nominating'])) {
                    $this->command->info("  Skipping week {$weekNumber}/{$year} — already {$existingWeek->status}");
                    continue;
                }

                $this->command->info("  Week {$weekNumber}/{$year} already exists — ensuring winners set.");

                $this->ensureWinnersForWeek($existingWeek, $artistSpotlights, $businessSpotlights, $allUsers);
                $artistWinnerCount += SpotlightWeekNominee::where('spotlight_week_id', $existingWeek->id)
                    ->where('is_winner', true)
                    ->where('spotlightable_type', ArtistSpotlight::class)
                    ->count();
                $businessWinnerCount += SpotlightWeekNominee::where('spotlight_week_id', $existingWeek->id)
                    ->where('is_winner', true)
                    ->where('spotlightable_type', BusinessSpotlight::class)
                    ->count();
                $weeksCreated++;
                continue;
            }

            // Create the week
            $week = SpotlightWeek::create([
                'week_number'      => $weekNumber,
                'year'             => $year,
                'status'           => 'completed',
                'voting_starts_at' => $weekStart,
                'voting_ends_at'   => $votingEnd,
                'announced_at'     => (clone $votingEnd)->addHours(2),
                'metadata'         => ['note' => "Historical winner week — {$monthsAgo} months ago"],
            ]);

            $this->ensureWinnersForWeek($week, $artistSpotlights, $businessSpotlights, $allUsers);

            $newArtists = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
                ->where('is_winner', true)
                ->where('spotlightable_type', ArtistSpotlight::class)
                ->count();
            $newBusinesses = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
                ->where('is_winner', true)
                ->where('spotlightable_type', BusinessSpotlight::class)
                ->count();

            $artistWinnerCount += $newArtists;
            $businessWinnerCount += $newBusinesses;
            $weeksCreated++;

            $this->command->info("  Created week {$weekNumber}/{$year} (voting: {$weekStart->format('M d')} – {$votingEnd->format('M d, Y')}) with {$newArtists} artist winner(s) and {$newBusinesses} business winner(s)");
        }

        $this->command->info('');
        $this->command->info("✅ Historical Winners Seeding Complete!");
        $this->command->info("  • {$weeksCreated} completed weeks seeded");
        $this->command->info("  • {$artistWinnerCount} artist winners");
        $this->command->info("  • {$businessWinnerCount} business winners");
        $this->command->info('');
        $this->command->info('  Try: GET /api/v1/spotlight/historical-winners?type=artist');
        $this->command->info('  Try: GET /api/v1/spotlight/historical-winners?type=business');
    }

    /**
     * Ensure the given week has at least one artist winner and one business winner.
     * Uses firstOrCreate to avoid duplicates on re-run.
     */
    private function ensureWinnersForWeek(
        SpotlightWeek $week,
        $artistSpotlights,
        $businessSpotlights,
        $allUsers
    ): void {
        // ── Artist winner ─────────────────────────────────────────
        $existingArtistWinner = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
            ->where('is_winner', true)
            ->where('spotlightable_type', ArtistSpotlight::class)
            ->first();

        if (! $existingArtistWinner) {
            $usedArtistIds = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
                ->where('spotlightable_type', ArtistSpotlight::class)
                ->pluck('spotlightable_id')
                ->toArray();

            $availableArtists = $artistSpotlights->whereNotIn('id', $usedArtistIds);
            $artistSpotlight = $availableArtists->isNotEmpty()
                ? $availableArtists->random()
                : $artistSpotlights->random();

            $artistUser = User::find($artistSpotlight->user_id) ?? $allUsers->random();
            $freeVotes = rand(20, 100);
            $paidVotes = rand(5, 30);
            $totalVotes = $freeVotes + $paidVotes;

            // Use firstOrCreate with the composite key to avoid duplicates on re-run
            $nominee = SpotlightWeekNominee::firstOrCreate(
                [
                    'spotlight_week_id'  => $week->id,
                    'spotlightable_type' => ArtistSpotlight::class,
                    'spotlightable_id'   => $artistSpotlight->id,
                ],
                [
                    'user_id'          => $artistUser->id,
                    'free_vote_count'  => $freeVotes,
                    'paid_vote_count'  => $paidVotes,
                    'total_vote_count' => $totalVotes,
                ]
            );

            // Ensure it's marked as winner with rank 1
            $nominee->update([
                'is_winner' => true,
                'rank'      => 1,
            ]);

            $this->ensureApplicationExists($week, $artistSpotlight, ArtistSpotlight::class, $artistUser);

            $name = $artistSpotlight->artist_stage_name ?? $artistSpotlight->full_legal_name;
            $this->command->info("    Created artist winner: {$name} ({$totalVotes} votes)");
        }

        // ── Business winner ───────────────────────────────────────
        $existingBusinessWinner = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
            ->where('is_winner', true)
            ->where('spotlightable_type', BusinessSpotlight::class)
            ->first();

        if (! $existingBusinessWinner) {
            $usedBusinessIds = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
                ->where('spotlightable_type', BusinessSpotlight::class)
                ->pluck('spotlightable_id')
                ->toArray();

            $availableBusinesses = $businessSpotlights->whereNotIn('id', $usedBusinessIds);
            $businessSpotlight = $availableBusinesses->isNotEmpty()
                ? $availableBusinesses->random()
                : $businessSpotlights->random();

            $businessUser = User::find($businessSpotlight->user_id) ?? $allUsers->random();
            $freeVotes = rand(20, 100);
            $paidVotes = rand(5, 30);
            $totalVotes = $freeVotes + $paidVotes;

            $nominee = SpotlightWeekNominee::firstOrCreate(
                [
                    'spotlight_week_id'  => $week->id,
                    'spotlightable_type' => BusinessSpotlight::class,
                    'spotlightable_id'   => $businessSpotlight->id,
                ],
                [
                    'user_id'          => $businessUser->id,
                    'free_vote_count'  => $freeVotes,
                    'paid_vote_count'  => $paidVotes,
                    'total_vote_count' => $totalVotes,
                ]
            );

            $nominee->update([
                'is_winner' => true,
                'rank'      => 1,
            ]);

            $this->ensureApplicationExists($week, $businessSpotlight, BusinessSpotlight::class, $businessUser);

            $name = $businessSpotlight->business_name ?? $businessSpotlight->owner_founder_name;
            $this->command->info("    Created business winner: {$name} ({$totalVotes} votes)");
        }

        // Set week-level winner references for consistency
        $artistWinner = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
            ->where('is_winner', true)
            ->where('spotlightable_type', ArtistSpotlight::class)
            ->first();

        $businessWinner = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
            ->where('is_winner', true)
            ->where('spotlightable_type', BusinessSpotlight::class)
            ->first();

        // Prefer business winner for the week-level winner field if both exist,
        // otherwise use whichever winner exists.
        $primaryWinner = $businessWinner ?? $artistWinner;
        if ($primaryWinner && ! $week->winner_spotlightable_type) {
            $week->updateQuietly([
                'winner_spotlightable_type' => $primaryWinner->spotlightable_type,
                'winner_spotlightable_id'   => $primaryWinner->spotlightable_id,
            ]);
        }

        // Ensure announced_at is set
        if (! $week->announced_at && $week->voting_ends_at) {
            $week->updateQuietly([
                'announced_at' => (clone $week->voting_ends_at)->addHours(2),
            ]);
        }
    }

    /**
     * Ensure a spotlight application exists for this week + spotlight combination.
     */
    private function ensureApplicationExists(
        SpotlightWeek $week,
        $spotlight,
        string $spotlightType,
        User $user
    ): void {
        try {
            SpotlightApplication::firstOrCreate(
                [
                    'spotlight_week_id'  => $week->id,
                    'spotlightable_type' => $spotlightType,
                    'spotlightable_id'   => $spotlight->id,
                ],
                [
                    'user_id'     => $user->id,
                    'status'      => 'selected',
                    'applied_at'  => $week->voting_starts_at?->copy()->subDays(rand(1, 5)) ?? now(),
                    'reviewed_at' => $week->voting_starts_at?->copy()->subDay() ?? now(),
                ]
            );
        } catch (\Exception $e) {
            // Silently skip duplicates
        }
    }
}
