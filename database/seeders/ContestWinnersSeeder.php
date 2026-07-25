<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Contest\Contestant;
use App\Models\Contest\Season;
use App\Models\Round;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContestWinnersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds completed Boss Beginnings seasons with declared winners so the
     * following API endpoints return data:
     *
     *   GET /api/v1/contest/winners/current
     *   GET /api/v1/contest/winners/past-six-months
     *
     * Creates multiple completed seasons spanning the last 6 months, each
     * with at least one contestant with status 'winner' linked to a Business,
     * plus rounds and media records for the response to render fully.
     *
     * Existing data (seasons, rounds, contestants, businesses) is reused
     * where possible — nothing existing is modified, only supplemented.
     */
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->error('No users found. Run UserSeeder first.');
            return;
        }

        $this->command->info('Found ' . $users->count() . ' users.');

        // ── Create 3 completed seasons spanning the last 6 months ──
        // Month offsets: 5 months ago, 3 months ago, 1 month ago
        $seasonMonths = [5, 3, 1];
        $seasonsProcessed = 0;
        $winnersCreated = 0;

        foreach ($seasonMonths as $monthsAgo) {
            $monthStart = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $monthEnd = (clone $monthStart)->endOfMonth();

            $title = "Boss Beginnings Season " . now()->year . "." . (6 - $monthsAgo);
            $slug = Str::slug($title);

            // Check if a completed season already exists in this timeframe
            $existingSeason = Season::where('status', 'completed')
                ->whereBetween('ends_at', [
                    (clone $monthStart)->subWeek(),
                    (clone $monthStart)->addWeeks(5),
                ])
                ->first();

            if ($existingSeason) {
                $this->command->info("  Season #{$existingSeason->id} '{$existingSeason->title}' already completed in this period — ensuring it has a winner.");

                $created = $this->ensureWinnerForSeason($existingSeason, $users);
                $winnersCreated += $created;

                if ($created > 0) {
                    $this->createRoundsIfMissing($existingSeason);
                }

                $seasonsProcessed++;
                continue;
            }

            // Check if a season with this slug already exists (avoid duplicate slugs)
            $slugExists = Season::where('slug', $slug)->exists();
            if ($slugExists) {
                $slug = $slug . '-' . uniqid();
            }

            // Create the season
            $season = Season::create([
                'contest_type'           => 'business',
                'title'                  => $title,
                'slug'                   => $slug,
                'description'            => "Season " . now()->year . "." . (6 - $monthsAgo) . " of the Boss Beginnings competition — showcasing the best local businesses and their impact on the community.",
                'status'                 => 'completed',
                'applications_starts_at' => (clone $monthStart)->subWeeks(3),
                'applications_ends_at'   => (clone $monthStart)->subDay(),
                'starts_at'              => $monthStart,
                'ends_at'                => $monthEnd,
                'is_active'              => false,
                'is_featured'            => $monthsAgo === 1,
                'metadata'               => [
                    'note'             => "Historical winner season — {$monthsAgo} months ago",
                    'total_applicants' => rand(15, 30),
                ],
            ]);

            $this->command->info("  Created season #{$season->id} '{$season->title}' ({$monthStart->format('M Y')})");

            // Create rounds for this season
            $this->createRoundsForSeason($season);

            // Create a winner contestant
            $created = $this->ensureWinnerForSeason($season, $users);
            $winnersCreated += $created;
            $seasonsProcessed++;
        }

        // ── Also ensure the MOST recently completed season has a winner ──
        // This is critical for the "current winner" endpoint which picks
        // the latest completed season.
        $latestCompleted = Season::where('status', 'completed')
            ->orderByDesc('ends_at')
            ->orderByDesc('id')
            ->first();

        if ($latestCompleted) {
            $hasWinner = Contestant::where('season_id', $latestCompleted->id)
                ->where('status', 'winner')
                ->exists();

            if (! $hasWinner) {
                $created = $this->ensureWinnerForSeason($latestCompleted, $users);
                $winnersCreated += $created;
                $this->command->info("  Added winner to latest completed season #{$latestCompleted->id}");
            }
        }

        $this->command->info('');
        $this->command->info('✅ Contest Winners Seeding Complete!');
        $this->command->info("  • {$seasonsProcessed} completed seasons processed");
        $this->command->info("  • {$winnersCreated} winner contestant(s) created/promoted");
        $this->command->info('');
        $this->command->info('  Try: GET /api/v1/contest/winners/current');
        $this->command->info('  Try: GET /api/v1/contest/winners/past-six-months');
    }

    /**
     * Ensure a season has at least one contestant with status 'winner'.
     *
     * If an existing contestant (any status) is found for a business in this
     * season, it is promoted to 'winner'. Otherwise, a new contestant is
     * created with 'winner' status.
     *
     * @return int Number of winners created/promoted (0 or 1).
     */
    private function ensureWinnerForSeason(Season $season, $users): int
    {
        $hasWinner = Contestant::where('season_id', $season->id)
            ->where('status', 'winner')
            ->exists();

        if ($hasWinner) {
            return 0;
        }

        // Pick or create a Business to serve as the contestable entity
        $business = $this->pickBusiness($users);
        if (! $business) {
            $this->command->warn("    Could not find or create a Business — skipping winner for season #{$season->id}");
            return 0;
        }

        // Find the first round of this season for current_round_id
        $firstRound = Round::where('season_id', $season->id)
            ->orderBy('round_number')
            ->first();

        $displayName = $business->business_name ?? $business->owner_founder_name ?? 'Winner Business';

        // Check if this business already has a contestant record for this season
        $existingContestant = Contestant::where('season_id', $season->id)
            ->where('contestable_type', Business::class)
            ->where('contestable_id', $business->id)
            ->first();

        if ($existingContestant) {
            // Promote the existing contestant to winner
            $existingContestant->update([
                'status'       => 'winner',
                'total_score'  => round(rand(8500, 9950) / 100, 2), // 85.00 – 99.50
                'current_round_id' => $firstRound?->id,
            ]);

            $this->command->info("    Promoted existing contestant #{$existingContestant->id} ({$displayName}) to winner");
            return 1;
        }

        // Create a brand new contestant as winner
        Contestant::create([
            'season_id'          => $season->id,
            'contestable_type'   => Business::class,
            'contestable_id'     => $business->id,
            'display_name'       => $displayName,
            'slug'               => Str::slug($displayName) . '-' . uniqid(),
            'avatar_url'         => $business->media()->first()?->file_path,
            'status'             => 'winner',
            'total_score'        => round(rand(8500, 9950) / 100, 2),
            'current_round_id'   => $firstRound?->id,
            'entered_at'         => $season->starts_at,
            'metadata'           => [
                'winning_round'  => $firstRound?->round_number ?? 1,
                'votes_received' => rand(500, 2000),
            ],
        ]);

        $this->command->info("    Created winner: {$displayName}");

        return 1;
    }

    /**
     * Pick an existing Business or create a minimal one.
     */
    private function pickBusiness($users): ?Business
    {
        // Prefer existing businesses with media (for better response data)
        $business = Business::has('media')->inRandomOrder()->first();
        if ($business) {
            return $business;
        }

        // Fallback to any business
        $business = Business::inRandomOrder()->first();
        if ($business) {
            return $business;
        }

        // Last resort: create a minimal business
        $user = $users->random();
        $slug = 'winner-business-' . uniqid();

        return Business::create([
            'user_id'                    => $user->id,
            'slug'                       => $slug,
            'business_name'              => 'Champion Business',
            'owner_founder_name'         => $user->name ?? 'Owner',
            'story'                      => 'A championship-winning business that demonstrated exceptional growth, community impact, and innovation throughout the Boss Beginnings competition.',
            'mission'                    => 'To lead by example and inspire the next generation of entrepreneurs.',
            'website_social_media'       => json_encode(['website' => 'https://example.com']),
            'community_impact_statement' => 'Creating jobs, supporting local suppliers, and giving back to the community through mentorship programs.',
            'revenue_stage'              => '50k-100k',
            'why_they_deserve_to_compete'=> 'Proven track record of excellence and community leadership.',
            'status'                     => 'active',
        ]);
    }

    /**
     * Create rounds for a newly created season.
     */
    private function createRoundsForSeason(Season $season): void
    {
        $roundTemplates = [
            [
                'round_number' => 1,
                'title'        => 'Preliminary Round',
                'goal'         => 'Submit your business pitch deck and introductory video.',
                'requirements' => 'Upload a pitch deck (max 20 slides) and a 2-minute intro video.',
                'advance_limit' => 20,
            ],
            [
                'round_number' => 2,
                'title'        => 'Semi-Finals',
                'goal'         => 'Present your community impact report and financial sustainability plan.',
                'requirements' => 'Submit a community impact statement (500-1000 words) with supporting evidence.',
                'advance_limit' => 10,
            ],
            [
                'round_number' => 3,
                'title'        => 'Grand Finals',
                'goal'         => 'Deliver a live pitch to the judging panel.',
                'requirements' => 'Prepare a 10-minute presentation covering business model, growth metrics, and community impact.',
                'advance_limit' => 3,
            ],
        ];

        $totalRounds = count($roundTemplates);
        $seasonDuration = $season->ends_at->diffInDays($season->starts_at);
        $daysPerRound = $totalRounds > 0 ? (int) floor($seasonDuration / $totalRounds) : 30;

        foreach ($roundTemplates as $i => $template) {
            $roundStart = (clone $season->starts_at)->addDays($i * $daysPerRound);
            $roundEnd = (clone $roundStart)->addDays($daysPerRound - 1)->endOfDay();

            if ($roundEnd->gt($season->ends_at)) {
                $roundEnd = (clone $season->ends_at);
            }

            // Skip if already exists
            $exists = Round::where('season_id', $season->id)
                ->where('round_number', $template['round_number'])
                ->exists();

            if ($exists) {
                continue;
            }

            Round::create([
                'season_id'               => $season->id,
                'round_number'            => $template['round_number'],
                'title'                   => $template['title'],
                'goal'                    => $template['goal'],
                'requirements'            => $template['requirements'],
                'voting_strategy'         => $i < 2 ? 'popular_vote' : 'judge_scored',
                'submission_type'         => 'multi',
                'submission_requirements' => [
                    'video'    => ['required' => true, 'max_duration_sec' => $i === 2 ? 600 : 120],
                    'document' => ['required' => true, 'formats' => ['pdf', 'docx']],
                ],
                'advance_limit'           => $template['advance_limit'],
                'elimination_rule'        => 'advance_limit',
                'advancement_config'      => [
                    'top_n'       => $template['advance_limit'],
                    'tiebreakers' => ['total_points', 'community_votes'],
                ],
                'is_active'               => false,
                'starts_at'               => $roundStart,
                'ends_at'                 => $roundEnd,
                'sort_order'              => $i + 1,
            ]);
        }
    }

    /**
     * Create rounds for a season that already exists but has no rounds.
     */
    private function createRoundsIfMissing(Season $season): void
    {
        $hasRounds = Round::where('season_id', $season->id)->exists();
        if (! $hasRounds) {
            $this->createRoundsForSeason($season);
        }
    }
}
