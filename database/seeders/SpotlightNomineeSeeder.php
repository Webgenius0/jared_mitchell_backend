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

class SpotlightNomineeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates up to 12 artist nominees and 12 business nominees for the
     * current voting week (or creates a voting week if none exists).
     * Uses existing ArtistSpotlight / BusinessSpotlight records.
     *
     * Run this after: UserSeeder, ArtistSpotlightSeeder, BusinessSpotlightSeeder
     */
    public function run(): void
    {
        // ── Gather existing spotlight records ─────────────────────────
        $artistSpotlights = ArtistSpotlight::whereIn('status', ['approved', 'submitted', 'under_review'])->get();
        $businessSpotlights = BusinessSpotlight::whereIn('status', ['approved', 'submitted', 'under_review'])->get();

        // Fallback to any spotlights if none approved
        if ($artistSpotlights->isEmpty()) {
            $artistSpotlights = ArtistSpotlight::all();
            if ($artistSpotlights->isEmpty()) {
                $this->command->error('SpotlightNomineeSeeder: No ArtistSpotlight records exist. Run ArtistSpotlightSeeder first.');
                return;
            }
            ArtistSpotlight::where('status', 'draft')->update(['status' => 'approved']);
            $artistSpotlights = ArtistSpotlight::all();
        }

        if ($businessSpotlights->isEmpty()) {
            $businessSpotlights = BusinessSpotlight::all();
            if ($businessSpotlights->isEmpty()) {
                $this->command->error('SpotlightNomineeSeeder: No BusinessSpotlight records exist. Run BusinessSpotlightSeeder first.');
                return;
            }
            BusinessSpotlight::where('status', 'draft')->update(['status' => 'approved']);
            $businessSpotlights = BusinessSpotlight::all();
        }

        $allUsers = User::all();
        if ($allUsers->isEmpty()) {
            $this->command->error('SpotlightNomineeSeeder: No users found. Run UserSeeder first.');
            return;
        }

        $this->command->info('Found ' . $artistSpotlights->count() . ' artist spotlights, ' . $businessSpotlights->count() . ' business spotlights.');

        // ── Get or create the current voting week ────────────────────
        $week = SpotlightWeek::votingOpen()->latest()->first();

        if (! $week) {
            // Try to find a voting week that hasn't started yet or recently ended
            $week = SpotlightWeek::whereIn('status', ['voting', 'nominating'])
                ->latest('voting_starts_at')
                ->first();
        }

        if (! $week) {
            // Create a new voting week starting this past Monday
            $weekStart = Carbon::now()->startOfWeek()->addDay(); // Monday
            $weekEnd = (clone $weekStart)->addDays(6)->endOfDay(); // Sunday

            $week = SpotlightWeek::firstOrCreate(
                ['week_number' => $weekStart->isoWeek(), 'year' => $weekStart->year],
                [
                    'status'            => 'voting',
                    'voting_starts_at'  => $weekStart,
                    'voting_ends_at'    => $weekEnd,
                    'metadata'          => ['note' => 'Created by SpotlightNomineeSeeder'],
                ]
            );

            $this->command->info("Created voting week #{$week->id} (W{$week->week_number}/{$week->year})");
        } else {
            $this->command->info("Using existing week #{$week->id} (W{$week->week_number}/{$week->year}, status: {$week->status})");
        }

        // ── Create applications and nominees for artists (up to 12) ──
        $artistCreated = 0;
        $selectedArtists = $artistSpotlights->take(12);

        foreach ($selectedArtists as $spotlight) {
            $userId = $spotlight->user_id ?? $allUsers->random()->id;

            // Ensure an application exists
            $app = SpotlightApplication::firstOrCreate(
                [
                    'spotlight_week_id'  => $week->id,
                    'spotlightable_type' => ArtistSpotlight::class,
                    'spotlightable_id'   => $spotlight->id,
                ],
                [
                    'user_id'    => $userId,
                    'status'     => 'selected',
                    'applied_at' => $week->voting_starts_at?->copy()->subDays(rand(1, 5)) ?? now(),
                ]
            );

            if ($app->wasRecentlyCreated || $app->status !== 'selected') {
                $app->update(['status' => 'selected', 'reviewed_at' => now()]);
            }

            // Create the nominee
            $freeVotes = rand(5, 50);
            $paidVotes = rand(0, 20);
            $totalVotes = $freeVotes + $paidVotes;

            $nominee = SpotlightWeekNominee::firstOrCreate(
                [
                    'spotlight_week_id'  => $week->id,
                    'spotlightable_type' => ArtistSpotlight::class,
                    'spotlightable_id'   => $spotlight->id,
                ],
                [
                    'user_id'          => $userId,
                    'free_vote_count'  => $freeVotes,
                    'paid_vote_count'  => $paidVotes,
                    'total_vote_count' => $totalVotes,
                    'is_winner'        => false,
                ]
            );

            if ($nominee->wasRecentlyCreated) {
                $artistCreated++;
                $name = $spotlight->artist_stage_name ?? $spotlight->full_legal_name;
                $this->command->info("  Added artist nominee: {$name}");
            }
        }

        // ── Create applications and nominees for businesses (up to 12) ──
        $businessCreated = 0;
        $selectedBusinesses = $businessSpotlights->take(12);

        foreach ($selectedBusinesses as $spotlight) {
            $userId = $spotlight->user_id ?? $allUsers->random()->id;

            // Ensure an application exists
            $app = SpotlightApplication::firstOrCreate(
                [
                    'spotlight_week_id'  => $week->id,
                    'spotlightable_type' => BusinessSpotlight::class,
                    'spotlightable_id'   => $spotlight->id,
                ],
                [
                    'user_id'    => $userId,
                    'status'     => 'selected',
                    'applied_at' => $week->voting_starts_at?->copy()->subDays(rand(1, 5)) ?? now(),
                ]
            );

            if ($app->wasRecentlyCreated || $app->status !== 'selected') {
                $app->update(['status' => 'selected', 'reviewed_at' => now()]);
            }

            // Create the nominee
            $freeVotes = rand(5, 50);
            $paidVotes = rand(0, 20);
            $totalVotes = $freeVotes + $paidVotes;

            $nominee = SpotlightWeekNominee::firstOrCreate(
                [
                    'spotlight_week_id'  => $week->id,
                    'spotlightable_type' => BusinessSpotlight::class,
                    'spotlightable_id'   => $spotlight->id,
                ],
                [
                    'user_id'          => $userId,
                    'free_vote_count'  => $freeVotes,
                    'paid_vote_count'  => $paidVotes,
                    'total_vote_count' => $totalVotes,
                    'is_winner'        => false,
                ]
            );

            if ($nominee->wasRecentlyCreated) {
                $businessCreated++;
                $name = $spotlight->business_name ?? $spotlight->owner_founder_name;
                $this->command->info("  Added business nominee: {$name}");
            }
        }

        // ── Summary ──────────────────────────────────────────────────
        $totalArtists = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
            ->where('spotlightable_type', ArtistSpotlight::class)
            ->count();
        $totalBusinesses = SpotlightWeekNominee::where('spotlight_week_id', $week->id)
            ->where('spotlightable_type', BusinessSpotlight::class)
            ->count();

        $this->command->info('');
        $this->command->info('✅ Spotlight Nominee Seeding Complete!');
        $this->command->info("  • Week #{$week->id} (W{$week->week_number}/{$week->year}) — {$week->status}");
        $this->command->info("  • {$totalArtists} artist nominees (new: {$artistCreated})");
        $this->command->info("  • {$totalBusinesses} business nominees (new: {$businessCreated})");
        $this->command->info('');
        $this->command->info('  Try: GET /api/v1/spotlight/nominated');
        $this->command->info('  Try: GET /api/v1/spotlight/nominated?type=artist');
        $this->command->info('  Try: GET /api/v1/spotlight/nominated?type=business');
    }
}
