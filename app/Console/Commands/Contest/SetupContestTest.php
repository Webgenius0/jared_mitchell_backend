<?php

namespace App\Console\Commands\Contest;

use App\Models\Business;
use App\Models\Contest\Contestant;
use App\Models\Contest\Season;
use App\Models\Round;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SetupContestTest extends Command
{
    protected $signature = 'contest:test-setup
                            {--fresh : Delete previous test data before creating new}
                            {--businesses=3 : Number of test businesses to create}
                            {--rounds=3 : Number of rounds (max 4)}';

    protected $description = 'Create a fully testable Boss Beginnings contest environment (season, rounds, boss user, businesses, contestants) with dates covering TODAY.';

    public const TEST_USER_EMAIL = 'contest_test@test.com';
    public const TEST_SEASON_SLUG_PREFIX = 'contest-test-season-';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->cleanup();
        }

        // ── 1. Test boss user (login: contest_test@test.com / 12345678) ──
        $user = User::firstOrCreate(
            ['email' => self::TEST_USER_EMAIL],
            [
                'phone'            => '01900000000',
                'password'         => Hash::make('12345678'),
                'status'           => 'active',
                'email_verified_at' => now(),
            ]
        );

        $bossRole = Role::firstOrCreate(['name' => 'boss', 'guard_name' => 'api']);
        if (! $user->hasRole('boss')) {
            $user->assignRole($bossRole);
        }

        // ── 2. Season with dates covering TODAY ──
        $season = Season::create([
            'contest_type'           => 'business',
            'title'                  => 'TEST SEASON ' . now()->format('Y-m-d H:i'),
            'slug'                   => self::TEST_SEASON_SLUG_PREFIX . now()->timestamp,
            'description'            => 'Auto-created test season (contest:test-setup)',
            'status'                 => 'in_progress',
            'configuration'          => ['max_contestants' => 50, 'voting_strategy' => 'popular_vote'],
            'applications_starts_at' => now()->subDays(10),
            'applications_ends_at'   => now()->addDays(10),
            'starts_at'              => now()->subDay(),
            'ends_at'                => now()->addDays(60),
            'is_active'              => true,
            'is_featured'            => true,
        ]);

        // ── 3. Rounds — round 1 covers today, later rounds follow ──
        $roundConfigs = [
            ['title' => 'Preliminary', 'advance_limit' => 2],
            ['title' => 'Qualifiers',  'advance_limit' => 2],
            ['title' => 'Semi-Finals', 'advance_limit' => 1],
            ['title' => 'Grand Finals','advance_limit' => 1],
        ];

        $roundCount = min(max((int) $this->option('rounds'), 2), 4);
        $start      = now()->subDay();
        $rounds     = [];

        foreach (array_slice($roundConfigs, 0, $roundCount) as $i => $cfg) {
            $rStart = $start->copy()->addDays($i * 13);
            $rEnd   = $start->copy()->addDays(($i + 1) * 13);

            $rounds[] = Round::create([
                'season_id'              => $season->id,
                'round_number'           => $i + 1,
                'title'                  => $cfg['title'],
                'goal'                   => "Submit your business materials for the {$cfg['title']} round.",
                'requirements'           => 'Pitch deck, intro video, and supporting documents.',
                'voting_strategy'        => 'popular_vote',
                'submission_type'        => 'multi',
                'advance_limit'          => $cfg['advance_limit'],
                'elimination_rule'       => 'advance_limit',
                'advancement_config'     => [
                    'top_n'                   => $cfg['advance_limit'],
                    'categories'              => ['Innovation', 'Presentation', 'Impact'],
                    'max_score_per_category'  => 10,
                ],
                'is_active'              => $i === 0,
                'starts_at'              => $rStart,
                'ends_at'                => $rEnd,
                'voting_ends_at'         => $rEnd->copy()->addDays(2),
                'sort_order'             => $i + 1,
            ]);
        }

        // ── 4. Businesses + approved applications + contestants in Round 1 ──
        $businessCount = max((int) $this->option('businesses'), 1);
        $businessIds   = [];

        for ($i = 1; $i <= $businessCount; $i++) {
            $business = Business::create([
                'user_id'                    => $user->id,
                'slug'                       => 'test-biz-' . $season->id . '-' . $i . '-' . Str::random(4),
                'business_name'              => "Test Business {$i}",
                'owner_founder_name'         => 'Owner ' . $i,
                'story'                      => "Test story for business {$i}.",
                'mission'                    => "Test mission for business {$i}.",
                'website_social_media'       => '{"website":"https://test' . $i . '.com"}',
                'community_impact_statement' => 'Test community impact statement.',
                'revenue_stage'              => '50k-100k',
                'why_they_deserve_to_compete'=> 'Test reason.',
                'status'                     => 'active',
            ]);
            $businessIds[] = $business->id;

            DB::table('contest_applications')->insert([
                'business_id' => $business->id,
                'season_id'   => $season->id,
                'status'      => 'approved',
                'approved_at' => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            Contestant::create([
                'season_id'          => $season->id,
                'contestable_type'   => Business::class,
                'contestable_id'     => $business->id,
                'display_name'       => $business->business_name,
                'slug'               => Str::slug($business->business_name) . '-' . Str::random(4),
                'status'             => 'active',
                'total_score'        => round(rand(500, 950) / 10, 2),
                'current_round_id'   => $rounds[0]->id,
                'entered_at'         => now(),
            ]);
        }

        $this->info('=== CONTEST TEST ENVIRONMENT READY ===');
        $this->table(
            ['Item', 'Value'],
            [
                ['Login email', self::TEST_USER_EMAIL],
                ['Login password', '12345678'],
                ['Season ID', $season->id],
                ['Season title', $season->title],
                ['Business IDs', implode(', ', $businessIds)],
                ['Round IDs', implode(', ', array_map(fn ($r) => $r->id . ' (' . $r->title . ')', $rounds))],
                ['Round 1 (open now)', $rounds[0]->id . ' — starts ' . $rounds[0]->starts_at->toDateTimeString() . ', ends ' . $rounds[0]->ends_at->toDateTimeString()],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Delete all data previously created by this command (safe — only touches
     * the test user + seasons with the test slug prefix + their children).
     */
    private function cleanup(): void
    {
        $testUserId = DB::table('users')->where('email', self::TEST_USER_EMAIL)->value('id');
        $seasonIds  = DB::table('seasons')->where('slug', 'like', self::TEST_SEASON_SLUG_PREFIX . '%')->pluck('id')->all();

        if (! empty($seasonIds)) {
            DB::table('contestants')->whereIn('season_id', $seasonIds)->delete();
            DB::table('contest_applications')->whereIn('season_id', $seasonIds)->delete();
            DB::table('rounds')->whereIn('season_id', $seasonIds)->delete();
            DB::table('seasons')->whereIn('id', $seasonIds)->delete();
        }

        if ($testUserId) {
            $businessIds = DB::table('businesses')->where('user_id', $testUserId)->pluck('id')->all();
            if (! empty($businessIds)) {
                DB::table('business_media')->whereIn('business_id', $businessIds)->delete();
                DB::table('business_interactions')->whereIn('business_id', $businessIds)->delete();
                DB::table('contest_applications')->whereIn('business_id', $businessIds)->delete();
                DB::table('contestants')->where('contestable_type', Business::class)->whereIn('contestable_id', $businessIds)->delete();
                DB::table('businesses')->where('user_id', $testUserId)->delete();
            }
            DB::table('model_has_roles')->where('model_type', User::class)->where('model_id', $testUserId)->delete();
            DB::table('users')->where('id', $testUserId)->delete();
        }

        $this->info('Previous test data cleaned.');
    }
}
