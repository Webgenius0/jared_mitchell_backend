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
        // 1. Create the new seasons table
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();

            // Core Identity
            $table->string('contest_type', 50)->default('business')->comment('business, artist, startup, etc.');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Status Machine: draft → open → in_progress → completed → archived
            $table->string('status', 30)->default('draft');

            // Configuration (JSON)
            $table->json('configuration')->nullable()->comment('max_contestants, voting_strategy, scoring_rules, etc.');

            // Timeline — separate application period from competition period
            $table->timestamp('applications_starts_at')->nullable();
            $table->timestamp('applications_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Display & Meta
            $table->boolean('is_active')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('contest_type');
            $table->index('is_active');
        });

        // 2. Migrate existing data from round_sessions → seasons
        if (Schema::hasTable('round_sessions')) {
            $roundSessions = DB::table('round_sessions')->orderBy('id')->get();

            foreach ($roundSessions as $session) {
                DB::table('seasons')->insert([
                    'contest_type' => 'business',
                    'title'                 => $session->title,
                    'slug'                  => $session->slug,
                    'description'           => $session->description,
                    'status'                => $session->is_active ? 'open' : 'draft',
                    'configuration'         => json_encode([
                        'max_contestants'  => 100,
                        'voting_strategy'  => 'popular_vote',
                        'scoring_rules'    => [
                            'clap'  => 1,
                            'save'  => 3,
                            'share' => 5,
                        ],
                    ]),
                    'starts_at'             => $session->starts_at,
                    'ends_at'               => $session->ends_at,
                    'is_active'             => $session->is_active,
                    'is_featured'           => false,
                    'metadata'              => json_encode([
                        'migrated_from_round_session' => true,
                        'original_round_session_id'   => $session->id,
                    ]),
                    'created_at'            => $session->created_at ?? now(),
                    'updated_at'            => $session->updated_at ?? now(),
                ]);
            }
        }

        // 3. Create a mapping table for the data migration
        // This allows existing foreign keys pointing to round_sessions
        // to be resolved to seasons during the transition period.
        if (Schema::hasTable('round_sessions')) {
            Schema::create('season_migration_map', function (Blueprint $table) {
                $table->unsignedBigInteger('round_session_id')->primary();
                $table->unsignedBigInteger('season_id');
                $table->timestamps();
            });

            // Populate the mapping
            DB::table('seasons')
                ->where('metadata->migrated_from_round_session', true)
                ->orderBy('id')
                ->each(function ($season) {
                    $meta = json_decode($season->metadata ?? '{}', true);
                    if (isset($meta['original_round_session_id'])) {
                        DB::table('season_migration_map')->insert([
                            'round_session_id' => $meta['original_round_session_id'],
                            'season_id'        => $season->id,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down(): void
    {
        Schema::dropIfExists('season_migration_map');
        Schema::dropIfExists('seasons');
    }
};
