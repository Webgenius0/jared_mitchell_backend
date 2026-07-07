<?php

namespace Tests\Feature\Contest;

use App\Jobs\Contest\ProcessRoundTransition;
use App\Models\Contest\Contestant;
use App\Models\Contest\RoundTransition;
use App\Models\Contest\Season;
use App\Models\Contest\Vote;
use App\Models\Round;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessRoundTransitionTest extends TestCase
{
    use RefreshDatabase;

    private function ensureRoundSessionExists(): void
    {
        if (!DB::table('round_sessions')->where('id', 1)->exists()) {
            DB::table('round_sessions')->insert([
                'id'         => 1,
                'title'      => 'Test Session',
                'slug'       => 'test-session',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /** @test */
    public function it_processes_a_round_transition_successfully()
    {
        Queue::fake();
        $this->ensureRoundSessionExists();

        $season = Season::factory()->create();
        $round1 = Round::factory()->for($season, 'season')->create([
            'round_number'      => 1,
            'sort_order'        => 1,
            'is_active'         => true,
            'ends_at'           => now()->subHour(),
            'elimination_rule'  => 'advance_limit',
            'advancement_config'=> ['advance_limit' => 2],
        ]);
        $round2 = Round::factory()->for($season, 'season')->create([
            'round_number'      => 2,
            'sort_order'        => 2,
            'is_active'         => false,
            'ends_at'           => now()->addDay(),
        ]);

        $c1 = Contestant::factory()->for($season)->active()->create([
            'display_name'     => 'Alice',
            'current_round_id' => $round1->id,
        ]);
        $c2 = Contestant::factory()->for($season)->active()->create([
            'display_name'     => 'Bob',
            'current_round_id' => $round1->id,
        ]);
        $c3 = Contestant::factory()->for($season)->active()->create([
            'display_name'     => 'Carol',
            'current_round_id' => $round1->id,
        ]);

        // Create users to vote (so leaderboard calculation produces scores)
        // Note: User model doesn't have a 'name' column, so we use create() directly
        $makeUser = function (string $email) {
            return User::create(['email' => $email, 'password' => bcrypt('password'), 'status' => 'active']);
        };
        $voter1 = $makeUser('voter1@test.com');
        $voter2 = $makeUser('voter2@test.com');
        $voter3 = $makeUser('voter3@test.com');
        $voter4 = $makeUser('voter4@test.com');
        $voter5 = $makeUser('voter5@test.com');

        $makeVote = function (User $voter, Contestant $contestant) use ($round1) {
            return ['user_id' => $voter->id, 'round_id' => $round1->id, 'votable_type' => Contestant::class, 'votable_id' => $contestant->id, 'vote_type' => 'upvote', 'weight' => 1];
        };

        // 5 votes for Alice
        Vote::create($makeVote($voter1, $c1));
        Vote::create($makeVote($voter2, $c1));
        Vote::create($makeVote($voter3, $c1));
        Vote::create($makeVote($voter4, $c1));
        Vote::create($makeVote($voter5, $c1));

        // 3 votes for Bob
        Vote::create($makeVote($voter1, $c2));
        Vote::create($makeVote($voter2, $c2));
        Vote::create($makeVote($voter3, $c2));

        // 1 vote for Carol
        Vote::create($makeVote($voter1, $c3));

        // Dispatch and process the job synchronously
        $job = new ProcessRoundTransition($round1);
        $job->handle($this->app->make(\App\Services\Contest\EliminationService::class));

        // Assert round 1 is no longer active
        $round1->refresh();
        $this->assertFalse($round1->is_active);

        // Assert round 2 is now active
        $round2->refresh();
        $this->assertTrue($round2->is_active);

        // Assert Alice and Bob advanced to round 2
        $c1->refresh();
        $c2->refresh();
        $c3->refresh();
        $this->assertEquals($round2->id, $c1->current_round_id);
        $this->assertEquals($round2->id, $c2->current_round_id);
        $this->assertEquals('active', $c1->status);
        $this->assertEquals('active', $c2->status);

        // Assert Carol was eliminated
        $this->assertEquals('eliminated', $c3->status);
        $this->assertEquals($round1->id, $c3->eliminated_in_round_id);

        // Assert transition was recorded
        $transition = RoundTransition::where('from_round_id', $round1->id)->first();
        $this->assertNotNull($transition);
        $this->assertEquals('completed', $transition->status);
        $this->assertEquals(2, $transition->advanced_count);
        $this->assertEquals(1, $transition->eliminated_count);
    }

    /** @test */
    public function it_skips_already_transitioned_round()
    {
        $this->ensureRoundSessionExists();
        $season = Season::factory()->create();
        $round = Round::factory()->for($season, 'season')->create([
            'is_active' => true,
            'ends_at'   => now()->subHour(),
        ]);

        RoundTransition::factory()->for($season)->create([
            'from_round_id' => $round->id,
            'status'        => 'completed',
        ]);

        $existingCount = RoundTransition::count();

        $job = new ProcessRoundTransition($round);
        $job->handle($this->app->make(\App\Services\Contest\EliminationService::class));

        // No new transitions created
        $this->assertEquals($existingCount, RoundTransition::count());
    }

    /** @test */
    public function it_skips_rounds_that_have_not_ended_yet()
    {
        $this->ensureRoundSessionExists();
        $season = Season::factory()->create();
        $round = Round::factory()->for($season, 'season')->create([
            'is_active' => true,
            'ends_at'   => now()->addDay(),  // Not ended yet
        ]);

        $existingCount = RoundTransition::count();

        $job = new ProcessRoundTransition($round);
        $job->handle($this->app->make(\App\Services\Contest\EliminationService::class));

        $this->assertEquals($existingCount, RoundTransition::count());
    }

    /** @test */
    public function it_still_processes_transition_with_zero_contestants()
    {
        $this->ensureRoundSessionExists();
        $season = Season::factory()->create();
        $round = Round::factory()->for($season, 'season')->create([
            'is_active'         => true,
            'ends_at'           => now()->subHour(),
            'elimination_rule'  => 'advance_limit',
            'advancement_config'=> [],
        ]);

        // No contestants or leaderboard entries
        $job = new ProcessRoundTransition($round);
        $job->handle($this->app->make(\App\Services\Contest\EliminationService::class));

        // A completed transition should be recorded (zero contestants = zero actions)
        $transition = RoundTransition::where('from_round_id', $round->id)
            ->where('status', 'completed')
            ->first();

        $this->assertNotNull($transition);
        $this->assertEquals(0, $transition->total_contestants);
        $this->assertEquals(0, $transition->advanced_count);
        $this->assertEquals(0, $transition->eliminated_count);

        // Round should be marked inactive
        $round->refresh();
        $this->assertFalse($round->is_active);
    }
}
