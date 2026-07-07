<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add new columns to the rounds table
        Schema::table('rounds', function (Blueprint $table) {

            // Season FK — replaces round_session_id
            $table->foreignId('season_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete();

            // Competition mechanics
            $table->string('voting_strategy', 50)
                  ->default('popular_vote')
                  ->after('requirements')
                  ->comment('popular_vote, judge_scored, weighted, admin_pick, single_elimination');

            $table->string('submission_type', 50)
                  ->default('multi')
                  ->after('voting_strategy')
                  ->comment('file_upload, video, link, text, multi');

            $table->json('submission_requirements')
                  ->nullable()
                  ->after('submission_type')
                  ->comment('Structured requirements: { video: { required: true, max_duration_sec: 180 } }');

            $table->string('elimination_rule', 50)
                  ->default('bottom_n')
                  ->after('advance_limit')
                  ->comment('bottom_n, top_percent, score_below_threshold, advance_limit, all_advance, single_elimination, admin_pick');

            $table->json('advancement_config')
                  ->nullable()
                  ->after('elimination_rule')
                  ->comment('{ top_n: 10, threshold: 80, percent: 25, tiebreakers: [...] }');

            // Extended timeline
            $table->timestamp('voting_ends_at')
                  ->nullable()
                  ->after('ends_at')
                  ->comment('Voting may extend beyond the submission deadline');

            // Ordering
            $table->integer('sort_order')
                  ->default(0)
                  ->after('voting_ends_at');

            // Flexible extension
            $table->json('metadata')
                  ->nullable()
                  ->after('sort_order');
        });

        // 2. Migrate data: map round_session_id → season_id
        if (Schema::hasTable('season_migration_map')) {
            DB::table('rounds')
                ->whereNull('season_id')
                ->orderBy('id')
                ->chunk(100, function ($rounds) {
                    foreach ($rounds as $round) {
                        $mapping = DB::table('season_migration_map')
                            ->where('round_session_id', $round->round_session_id)
                            ->first();

                        if ($mapping) {
                            DB::table('rounds')
                                ->where('id', $round->id)
                                ->update(['season_id' => $mapping->season_id]);
                        }
                    }
                });
        }

        // 3. Update the unique constraint
        // Drop the old constraint (composite on round_session_id + round_number)
        // MySQL/PostgreSQL syntax varies — use a try/catch approach
        try {
            // MySQL syntax
            Schema::table('rounds', function (Blueprint $table) {
                $table->dropUnique(['round_session_id', 'round_number']);
            });
        } catch (\Exception $e) {
            // SQLite doesn't support dropUnique on non-unique columns,
            // or the index may have a different name. Try the default naming.
            try {
                Schema::table('rounds', function (Blueprint $table) {
                    $table->dropUnique('rounds_round_session_id_round_number_unique');
                });
            } catch (\Exception $e2) {
                // Ignore — the index may not exist or may have been auto-named
            }
        }

        // Add the new unique constraint
        try {
            Schema::table('rounds', function (Blueprint $table) {
                $table->unique(['season_id', 'round_number']);
            });
        } catch (\Exception $e) {
            // Some rows may have duplicate (season_id, round_number) after migration.
            // This should not happen if the migration is clean, but handle gracefully.
            echo 'Note: Could not add unique constraint. Check for duplicate (season_id, round_number) pairs.' . PHP_EOL;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'season_id',
                'voting_strategy',
                'submission_type',
                'submission_requirements',
                'elimination_rule',
                'advancement_config',
                'voting_ends_at',
                'sort_order',
                'metadata',
            ]);

            // Restore old unique constraint
            try {
                $table->dropUnique(['season_id', 'round_number']);
            } catch (\Exception $e) {
                // May or may not exist
            }

            $table->unique(['round_session_id', 'round_number']);
        });
    }
};
