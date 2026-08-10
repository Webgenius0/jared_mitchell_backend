<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds an optional round_id to business_interactions so clap/save/share
     * interactions are tracked round-wise. This lets the contest leaderboard
     * show per-round counts that start at 0 for every new session, while the
     * legacy behaviour (round_id = NULL) is fully preserved for interactions
     * that happen outside of a contest round.
     *
     * The unique constraint now includes round_id, so the same user can clap /
     * save / share a business once per round. Existing rows keep round_id NULL
     * and stay unique exactly as before (NULLs are distinct in MySQL unique
     * indexes), so nothing in the current system changes.
     */
    public function up(): void
    {
        Schema::table('business_interactions', function (Blueprint $table) {
            // Give the user_id foreign key its own index FIRST — the composite
            // unique index is currently the index backing that FK, so MySQL
            // refuses to drop it (error 1553) until another index can take over.
            $table->index('user_id', 'business_interactions_user_id_index');

            $table->foreignId('round_id')
                ->after('business_id')
                ->nullable()
                ->constrained('rounds')
                ->nullOnDelete();
        });

        Schema::table('business_interactions', function (Blueprint $table) {
            $table->dropUnique('business_interactions_user_id_business_id_action_type_unique');

            $table->unique(
                ['user_id', 'business_id', 'action_type', 'round_id'],
                'business_interactions_user_business_action_round_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_interactions', function (Blueprint $table) {
            $table->dropUnique('business_interactions_user_business_action_round_unique');

            $table->unique(['user_id', 'business_id', 'action_type']);

            $table->dropIndex('business_interactions_user_id_index');
        });

        Schema::table('business_interactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('round_id');
        });
    }
};
