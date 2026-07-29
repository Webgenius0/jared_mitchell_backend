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

class PastSixMonthsWinnersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates 12 completed Boss Beginnings seasons spread evenly across the past 6 months,
     * each with a winner contestant (avatar_url = null → default image), so that the
     * following API endpoint returns 10+ winners:
     *
     *   GET /api/v1/contest/winners/past-six-months
     *
     * Day offsets are chosen to AVOID overlapping existing seasons.
     * Existing data is never modified — only supplemented.
     */
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->error('No users found. Run UserSeeder first.');
            return;
        }

        $this->command->info('Found ' . $users->count() . ' users.');

        // ── Day offsets spread evenly across the last 6 months ──
        // Avoids existing season dates (Feb 28, Apr 12, Apr 30, Jun 7, Jun 30)
        // Uses 2-week seasons at these positions:
        $dayOffsets = [178, 163, 148, 133, 118, 103, 88, 73, 58, 43, 28, 13];

        $seasonsCreated = 0;
        $winnersCreated = 0;

        foreach ($dayOffsets as $daysAgo) {
            $seasonStart = Carbon::now()->subDays($daysAgo)->startOfDay();
            $seasonEnd = (clone $seasonStart)->addDays(13)->endOfDay(); // 2-week season

            $title = 'Boss Beginnings - Season ' . $seasonStart->format('M d, Y');
            $slug = Str::slug($title);

            // Ensure unique slug
            if (Season::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . uniqid();
            }

            // Create the season
            $season = Season::create([
                'contest_type'           => 'business',
                'title'                  => $title,
                'slug'                   => $slug,
                'description'            => 'A Boss Beginnings competition season starting ' . $seasonStart->format('F j, Y') . ' — showcasing outstanding local businesses.',
                'status'                 => 'completed',
                'applications_starts_at' => (clone $seasonStart)->subWeeks(2),
                'applications_ends_at'   => (clone $seasonStart)->subDay(),
                'starts_at'              => $seasonStart,
                'ends_at'                => $seasonEnd,
                'is_active'              => false,
                'is_featured'            => false,
                'metadata'               => [
                    'note'             => 'Seeded winner season — ' . $daysAgo . ' days ago',
                    'total_applicants' => rand(10, 25),
                ],
            ]);

            $this->command->info("  Created season #{$season->id} '{$season->title}'");

            // Create rounds for this season
            $this->createRoundsForSeason($season);

            // Create a winner contestant (avatar_url = null for default image)
            $created = $this->createWinner($season, $users);
            $winnersCreated += $created;
            $seasonsCreated++;
        }

        // ── Final tally ──
        $totalWinners = Contestant::where('status', 'winner')
            ->whereHas('season', function ($q) {
                $q->where('status', 'completed')
                    ->where('ends_at', '>=', now()->subMonths(6));
            })
            ->count();

        $this->command->info('');
        $this->command->info('✅ Past Six Months Winners Seeding Complete!');
        $this->command->info("  • {$seasonsCreated} new seasons created");
        $this->command->info("  • {$winnersCreated} winner contestant(s) created");
        $this->command->info("  • {$totalWinners} total winners in past 6 months");
        $this->command->info('');
        $this->command->info('  Try: GET /api/v1/contest/winners/past-six-months');
    }

    /**
     * Create a winner contestant for a season (with null avatar_url for default image).
     */
    private function createWinner(Season $season, $users): int
    {
        $business = $this->pickBusiness($users);
        if (!$business) {
            $this->command->warn("    Could not find or create a Business — skipping season #{$season->id}");
            return 0;
        }

        $firstRound = Round::where('season_id', $season->id)
            ->orderBy('round_number')
            ->first();

        $displayName = $business->business_name ?? $business->owner_founder_name ?? 'Winner Business';

        Contestant::create([
            'season_id'          => $season->id,
            'contestable_type'   => Business::class,
            'contestable_id'     => $business->id,
            'display_name'       => $displayName,
            'slug'               => Str::slug($displayName) . '-' . uniqid(),
            'avatar_url'         => null, // Default image will be used by the API
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
        // Reuse existing businesses to avoid cluttering the database
        $business = Business::inRandomOrder()->first();
        if ($business) {
            return $business;
        }

        // Last resort: create a minimal business
        $user = $users->random();

        return Business::create([
            'user_id'                    => $user->id,
            'slug'                       => 'seeded-winner-business-' . uniqid(),
            'business_name'              => 'Winner Business #' . rand(100, 999),
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
     * Create 3 rounds for a newly created season.
     */
    private function createRoundsForSeason(Season $season): void
    {
        $roundTemplates = [
            ['round_number' => 1, 'title' => 'Preliminary Round', 'goal' => 'Submit your business pitch deck and introductory video.', 'requirements' => 'Upload a pitch deck (max 20 slides) and a 2-minute intro video.', 'advance_limit' => 20],
            ['round_number' => 2, 'title' => 'Semi-Finals', 'goal' => 'Present your community impact report and financial sustainability plan.', 'requirements' => 'Submit a community impact statement (500-1000 words) with supporting evidence.', 'advance_limit' => 10],
            ['round_number' => 3, 'title' => 'Grand Finals', 'goal' => 'Deliver a live pitch to the judging panel.', 'requirements' => 'Prepare a 10-minute presentation covering business model, growth metrics, and community impact.', 'advance_limit' => 3],
        ];

        $seasonDuration = $season->ends_at->diffInDays($season->starts_at);
        $daysPerRound = (int) floor($seasonDuration / count($roundTemplates));

        foreach ($roundTemplates as $i => $template) {
            $roundStart = (clone $season->starts_at)->addDays($i * $daysPerRound);
            $roundEnd = (clone $roundStart)->addDays($daysPerRound - 1)->endOfDay();

            if ($roundEnd->gt($season->ends_at)) {
                $roundEnd = (clone $season->ends_at);
            }

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
}
