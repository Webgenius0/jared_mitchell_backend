<?php

namespace Tests\Feature\Contest;

use App\Models\Contest\Contestant;
use App\Models\Contest\LeaderboardEntry;
use App\Models\Contest\RoundTransition;
use App\Models\Contest\Season;
use App\Models\Round;
use App\Services\Contest\EliminationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EliminationServiceTest extends TestCase
{
    use RefreshDatabase;

    private EliminationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(EliminationService::class);
    }

    // ── Helper: Build a leaderboard collection with scores ──

    /**
     * Ensure a record exists in round_sessions for the FK constraint.
     * The RoundFactory sets round_session_id=1, so we need id=1 here.
     */
    private function ensureRoundSessionExists(): void
    {
        if (!\Illuminate\Support\Facades\DB::table('round_sessions')->where('id', 1)->exists()) {
            \Illuminate\Support\Facades\DB::table('round_sessions')->insert([
                'id'         => 1,
                'title'      => 'Test Session',
                'slug'       => 'test-session',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function makeLeaderboard(array $scoresWithRanks): array
    {
        $entries = [];
        $this->ensureRoundSessionExists();
        $season = Season::factory()->create();

        foreach ($scoresWithRanks as $i => $data) {
            $contestant = Contestant::factory()
                ->for($season)
                ->create([
                    'display_name' => $data['name'] ?? 'Contestant ' . $i,
                    'slug'         => 'contestant-' . ($i + 1) . '-' . \Illuminate\Support\Str::random(4),
                ]);

            $entry = LeaderboardEntry::factory()
                ->for($season)
                ->for($contestant, 'contestant')
                ->create([
                    'rank'        => $data['rank'],
                    'total_score' => $data['score'],
                    'votes_count'  => $data['votes'] ?? 0,
                    'snapshot'    => ['display_name' => $contestant->display_name],
                ]);

            $entries[] = $entry;
        }

        return $entries;
    }

    // ── All 7 Elimination Rules ──

    /** @test */
    public function it_advances_top_n_contestants_with_advance_limit_rule()
    {
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 100, 'votes' => 50],
            ['name' => 'Bob',   'rank' => 2, 'score' => 80,  'votes' => 40],
            ['name' => 'Carol', 'rank' => 3, 'score' => 60,  'votes' => 30],
            ['name' => 'Dave',  'rank' => 4, 'score' => 40,  'votes' => 20],
            ['name' => 'Eve',   'rank' => 5, 'score' => 20,  'votes' => 10],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'advance_limit',
            'advancement_config' => ['advance_limit' => 3],
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        $this->assertCount(3, $result['advanced']);
        $this->assertCount(2, $result['eliminated']);
        $advancedNames = array_column($result['advanced'], 'display_name');
        $this->assertContains('Alice', $advancedNames);
        $this->assertContains('Bob', $advancedNames);
        $this->assertContains('Carol', $advancedNames);
        $this->assertNotContains('Dave', $advancedNames);
        $this->assertNotContains('Eve', $advancedNames);
    }

    /** @test */
    public function it_eliminates_bottom_n_contestants()
    {
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 100],
            ['name' => 'Bob',   'rank' => 2, 'score' => 80],
            ['name' => 'Carol', 'rank' => 3, 'score' => 60],
            ['name' => 'Dave',  'rank' => 4, 'score' => 40],
            ['name' => 'Eve',   'rank' => 5, 'score' => 20],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'bottom_n',
            'advancement_config' => ['eliminate_count' => 2],
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        $this->assertCount(3, $result['advanced']);
        $this->assertCount(2, $result['eliminated']);
        $eliminatedNames = array_column($result['eliminated'], 'display_name');
        $this->assertContains('Dave', $eliminatedNames);
        $this->assertContains('Eve', $eliminatedNames);
    }

    /** @test */
    public function it_keeps_top_percent_of_contestants()
    {
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 100],
            ['name' => 'Bob',   'rank' => 2, 'score' => 90],
            ['name' => 'Carol', 'rank' => 3, 'score' => 80],
            ['name' => 'Dave',  'rank' => 4, 'score' => 70],
            ['name' => 'Eve',   'rank' => 5, 'score' => 60],
            ['name' => 'Frank', 'rank' => 6, 'score' => 50],
            ['name' => 'Grace', 'rank' => 7, 'score' => 40],
            ['name' => 'Heidi', 'rank' => 8, 'score' => 30],
            ['name' => 'Ivan',  'rank' => 9, 'score' => 20],
            ['name' => 'Judy',  'rank' => 10, 'score' => 10],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'top_percent',
            'advancement_config' => ['keep_percent' => 30],
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        // 30% of 10 = 3 (ceil)
        $this->assertCount(3, $result['advanced']);
        $this->assertCount(7, $result['eliminated']);
    }

    /** @test */
    public function it_eliminates_contestants_below_score_threshold()
    {
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 85],
            ['name' => 'Bob',   'rank' => 2, 'score' => 72],
            ['name' => 'Carol', 'rank' => 3, 'score' => 55],
            ['name' => 'Dave',  'rank' => 4, 'score' => 42],
            ['name' => 'Eve',   'rank' => 5, 'score' => 18],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'score_below_threshold',
            'advancement_config' => ['score_threshold' => 50],
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        $this->assertCount(3, $result['advanced']);  // 85, 72, 55
        $this->assertCount(2, $result['eliminated']); // 42, 18
    }

    /** @test */
    public function it_keeps_only_the_winner_in_single_elimination()
    {
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 100],
            ['name' => 'Bob',   'rank' => 2, 'score' => 90],
            ['name' => 'Carol', 'rank' => 3, 'score' => 80],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'single_elimination',
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        $this->assertCount(1, $result['advanced']);
        $this->assertCount(2, $result['eliminated']);
        $this->assertEquals('Alice', $result['advanced'][0]['display_name']);
    }

    /** @test */
    public function it_advances_all_contestants_with_all_advance_rule()
    {
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 100],
            ['name' => 'Bob',   'rank' => 2, 'score' => 50],
            ['name' => 'Carol', 'rank' => 3, 'score' => 10],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'all_advance',
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        $this->assertCount(3, $result['advanced']);
        $this->assertCount(0, $result['eliminated']);
    }

    /** @test */
    public function it_returns_empty_arrays_for_admin_pick_rule()
    {
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 100],
            ['name' => 'Bob',   'rank' => 2, 'score' => 50],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'admin_pick',
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        $this->assertCount(0, $result['advanced']);
        $this->assertCount(0, $result['eliminated']);
    }

    // ── Tie-Breaking Scenarios ──

    /** @test */
    public function it_handles_ties_at_cutoff_with_all_tied_advance()
    {
        // Scores: 100, 90, 80, 80, 50 — cutoff at position 3 (advance limit = 3)
        // Contestants at rank 3 and 4 are both score 80 — tied
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 100],
            ['name' => 'Bob',   'rank' => 2, 'score' => 90],
            ['name' => 'Carol', 'rank' => 3, 'score' => 80],
            ['name' => 'Dave',  'rank' => 3, 'score' => 80],   // tied with Carol
            ['name' => 'Eve',   'rank' => 5, 'score' => 50],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'advance_limit',
            'advancement_config' => [
                'advance_limit' => 3,
                'cutoff_tie_breaker' => 'all_tied_advance',
            ],
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        // All contestants tied at the cutoff advance (Carol and Dave join Alice, Bob)
        $this->assertCount(4, $result['advanced']);
        $this->assertCount(1, $result['eliminated']); // Only Eve is eliminated
    }

    /** @test */
    public function it_handles_ties_at_cutoff_with_all_tied_eliminate()
    {
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 100],
            ['name' => 'Bob',   'rank' => 2, 'score' => 90],
            ['name' => 'Carol', 'rank' => 3, 'score' => 80],
            ['name' => 'Dave',  'rank' => 3, 'score' => 80],
            ['name' => 'Eve',   'rank' => 5, 'score' => 50],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'advance_limit',
            'advancement_config' => [
                'advance_limit' => 3,
                'cutoff_tie_breaker' => 'all_tied_eliminate',
            ],
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        // Alice, Bob advance. Carol and Dave tied at cutoff — eliminated.
        // Eve fills the 3rd remaining slot.
        $this->assertCount(3, $result['advanced']);
        $this->assertCount(2, $result['eliminated']);
        $advancedNames = array_column($result['advanced'], 'display_name');
        $this->assertContains('Alice', $advancedNames);
        $this->assertContains('Bob', $advancedNames);
        $this->assertContains('Eve', $advancedNames);
    }

    /** @test */
    public function it_handles_ties_above_cutoff_with_all_tied_advance()
    {
        // Scores: 100, 100, 90, 80 — cutoff at position 2 (advance limit = 2)
        // Both score-100 contestants have same rank. They're above the cutoff boundary
        // but tied with each other. With all_tied_advance, both should advance.
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 100],
            ['name' => 'Bob',   'rank' => 1, 'score' => 100],  // tied with Alice
            ['name' => 'Carol', 'rank' => 3, 'score' => 90],
            ['name' => 'Dave',  'rank' => 4, 'score' => 80],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'advance_limit',
            'advancement_config' => [
                'advance_limit' => 2,
                'cutoff_tie_breaker' => 'all_tied_advance',
            ],
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        // No capacity issue: 2 tie zone contestants fit within 2 slots
        $this->assertCount(2, $result['advanced']);
        $this->assertCount(2, $result['eliminated']);
        $advancedNames = array_column($result['advanced'], 'display_name');
        $this->assertContains('Alice', $advancedNames);
        $this->assertContains('Bob', $advancedNames);
    }

    /** @test */
    public function it_handles_ties_above_cutoff_with_all_tied_eliminate()
    {
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 100],
            ['name' => 'Bob',   'rank' => 1, 'score' => 100],
            ['name' => 'Carol', 'rank' => 3, 'score' => 90],
            ['name' => 'Dave',  'rank' => 4, 'score' => 80],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'advance_limit',
            'advancement_config' => [
                'advance_limit' => 2,
                'cutoff_tie_breaker' => 'all_tied_eliminate',
            ],
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        // 2 tie zone contestants fit within 2 slots — both advance regardless of breaker
        $this->assertCount(2, $result['advanced']);
        $this->assertCount(2, $result['eliminated']);
        $advancedNames = array_column($result['advanced'], 'display_name');
        $this->assertContains('Alice', $advancedNames);
        $this->assertContains('Bob', $advancedNames);
    }

    /** @test */
    public function it_handles_three_way_tie_at_cutoff()
    {
        // Three contestants tied at score 70, ranked 3 — cutoff at position 3
        $leaderboard = $this->makeLeaderboard([
            ['name' => 'Alice', 'rank' => 1, 'score' => 100],
            ['name' => 'Bob',   'rank' => 2, 'score' => 85],
            ['name' => 'Carol', 'rank' => 3, 'score' => 70],
            ['name' => 'Dave',  'rank' => 3, 'score' => 70],
            ['name' => 'Eve',   'rank' => 3, 'score' => 70],
            ['name' => 'Frank', 'rank' => 6, 'score' => 40],
        ]);
        $round = Round::factory()->create([
            'elimination_rule' => 'advance_limit',
            'advancement_config' => [
                'advance_limit' => 3,
                'cutoff_tie_breaker' => 'all_tied_advance',
            ],
        ]);

        $result = $this->service->applyEliminationRule($round, collect($leaderboard));

        $this->assertCount(5, $result['advanced']); // Alice, Bob + 3 tied at cutoff
        $this->assertCount(1, $result['eliminated']); // Frank
    }

    // ── findNextRound (the orWhere fix) ──

    /** @test */
    public function find_next_round_does_not_return_rounds_from_other_seasons()
    {
        $this->ensureRoundSessionExists();
        $season1 = Season::factory()->create(['title' => 'Season 1']);
        $season2 = Season::factory()->create(['title' => 'Season 2']);

        $round1S1 = Round::factory()->for($season1, 'season')->create([
            'round_number' => 1,
            'sort_order'   => 1,
            'ends_at'      => now()->subDay(),
        ]);
        $round2S1 = Round::factory()->for($season1, 'season')->create([
            'round_number' => 2,
            'sort_order'   => 2,
            'ends_at'      => now()->addDay(),
        ]);
        // Season 2 has a round with round_number > 1 — should NOT be found
        Round::factory()->for($season2, 'season')->create([
            'round_number' => 5,
            'sort_order'   => 5,
            'ends_at'      => now()->addDay(),
        ]);

        // Use reflection to test the private method
        $ref = new \ReflectionMethod($this->service, 'findNextRound');
        $ref->setAccessible(true);
        $nextRound = $ref->invoke($this->service, $round1S1);

        $this->assertNotNull($nextRound);
        $this->assertEquals($round2S1->id, $nextRound->id);
        // Ensure it's from Season 1, not Season 2
        $this->assertEquals($season1->id, $nextRound->season_id);
    }

    /** @test */
    public function find_next_round_returns_null_when_no_next_round_exists()
    {
        $this->ensureRoundSessionExists();
        $season = Season::factory()->create();
        $onlyRound = Round::factory()->for($season, 'season')->create([
            'round_number' => 1,
            'sort_order'   => 1,
            'round_session_id' => 1,
        ]);

        $ref = new \ReflectionMethod($this->service, 'findNextRound');
        $ref->setAccessible(true);
        $nextRound = $ref->invoke($this->service, $onlyRound);

        $this->assertNull($nextRound);
    }

    /** @test */
    public function find_next_round_finds_by_sort_order_when_round_numbers_are_same()
    {
        $this->ensureRoundSessionExists();
        $season = Season::factory()->create();
        $round1 = Round::factory()->for($season, 'season')->create([
            'round_number' => 1,
            'sort_order'   => 1,
            'round_session_id' => 1,
            'ends_at'      => now()->subDay(),
        ]);
        $round2 = Round::factory()->for($season, 'season')->create([
            'round_number' => 2,
            'sort_order'   => 2,
            'round_session_id' => 1,
            'ends_at'      => now()->addDay(),
        ]);

        $ref = new \ReflectionMethod($this->service, 'findNextRound');
        $ref->setAccessible(true);
        $nextRound = $ref->invoke($this->service, $round1);

        $this->assertNotNull($nextRound);
        $this->assertEquals($round2->id, $nextRound->id);
    }

    /** @test */
    public function find_next_round_prefers_higher_round_number_over_sort_order()
    {
        $this->ensureRoundSessionExists();
        $season = Season::factory()->create();
        $round1 = Round::factory()->for($season, 'season')->create([
            'round_number' => 1,
            'sort_order'   => 10,
            'round_session_id' => 1,
            'ends_at'      => now()->subDay(),
        ]);
        $round2 = Round::factory()->for($season, 'season')->create([
            'round_number' => 2,
            'sort_order'   => 5,
            'round_session_id' => 1,
            'ends_at'      => now()->addDay(),
        ]);
        $round3 = Round::factory()->for($season, 'season')->create([
            'round_number' => 3,
            'sort_order'   => 11,
            'round_session_id' => 1,
            'ends_at'      => now()->addDay(),
        ]);

        $ref = new \ReflectionMethod($this->service, 'findNextRound');
        $ref->setAccessible(true);
        $nextRound = $ref->invoke($this->service, $round1);

        // Should find round 2 (round_number=2) before round 3 (round_number=1, sort_order=11)
        $this->assertNotNull($nextRound);
        $this->assertEquals($round2->id, $nextRound->id);
    }

    // ── findRoundsNeedingTransition ──

    /** @test */
    public function it_finds_rounds_that_have_ended_and_need_transition()
    {
        $this->ensureRoundSessionExists();
        $season = Season::factory()->create();
        $endedRound = Round::factory()->for($season, 'season')->create([
            'round_number' => 1,
            'round_session_id' => 1,
            'is_active' => true,
            'ends_at'   => now()->subHour(),
        ]);
        $activeRound = Round::factory()->for($season, 'season')->create([
            'round_number' => 2,
            'round_session_id' => 1,
            'is_active' => true,
            'ends_at'   => now()->addDay(),
        ]);

        $rounds = $this->service->findRoundsNeedingTransition();

        $this->assertCount(1, $rounds);
        $this->assertEquals($endedRound->id, $rounds[0]->id);
    }

    /** @test */
    public function it_skips_rounds_that_already_have_a_completed_transition()
    {
        $this->ensureRoundSessionExists();
        $season = Season::factory()->create();
        $endedRound = Round::factory()->for($season, 'season')->create([
            'round_number' => 1,
            'round_session_id' => 1,
            'is_active' => true,
            'ends_at'   => now()->subHour(),
        ]);
        // Create a completed transition for this round
        RoundTransition::factory()->for($season)->create([
            'from_round_id' => $endedRound->id,
            'status'        => 'completed',
        ]);

        $rounds = $this->service->findRoundsNeedingTransition();

        $this->assertCount(0, $rounds);
    }

    // ── processRoundTransition (end-to-end via reflection for private methods) ──

    /** @test */
    public function it_finalizes_season_correctly_when_no_next_round()
    {
        $this->ensureRoundSessionExists();
        $season = Season::factory()->create();
        $finalRound = Round::factory()->for($season, 'season')->create([
            'round_number' => 1,
            'sort_order'   => 1,
            'round_session_id' => 1,
        ]);

        // Create 3 active contestants
        $contestantModels = collect();
        foreach (['Winner', 'RunnerUp', 'Finalist1'] as $name) {
            $contestantModels->push(Contestant::factory()
                ->for($season)
                ->active()
                ->create([
                    'display_name'     => $name,
                    'current_round_id' => $finalRound->id,
                ]));
        }

        $ref = new \ReflectionMethod($this->service, 'finalizeSeason');
        $ref->setAccessible(true);
        $ref->invoke($this->service, $finalRound);

        // Refresh from DB
        $season->refresh();
        $this->assertEquals('completed', $season->status);

        Contestant::query()->whereIn('id', $contestantModels->pluck('id'))->get()->each(
            function ($c) {
                if ($c->display_name === 'Winner') {
                    $this->assertEquals('winner', $c->status);
                } elseif ($c->display_name === 'RunnerUp') {
                    $this->assertEquals('runner_up', $c->status);
                } else {
                    $this->assertEquals('finalist', $c->status);
                }
            }
        );
    }

    /** @test */
    public function it_marks_third_and_below_as_finalists_in_season_finalization()
    {
        $this->ensureRoundSessionExists();
        $season = Season::factory()->create();
        $finalRound = Round::factory()->for($season, 'season')->create([
            'round_number' => 1,
            'round_session_id' => 1,
        ]);

        Contestant::factory()->for($season)->active()
            ->count(5)->create(['current_round_id' => $finalRound->id]);

        $ref = new \ReflectionMethod($this->service, 'finalizeSeason');
        $ref->setAccessible(true);
        $ref->invoke($this->service, $finalRound);

        // Contestants 3, 4, 5 should be 'finalist'
        $finalists = Contestant::where('status', 'finalist')->get();
        $this->assertCount(3, $finalists);
    }
}
