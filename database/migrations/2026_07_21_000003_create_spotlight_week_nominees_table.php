<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Spotlight Week Nominees — the Top 12 selected for a given week.
     * Populated when admin selects nominees from applications.
     * Caches vote counts for real-time leaderboard performance.
     */
    public function up(): void
    {
        Schema::create('spotlight_week_nominees', function (Blueprint $table) {
            $table->id();

            // Which week
            $table->foreignId('spotlight_week_id')
                  ->constrained('spotlight_weeks')
                  ->cascadeOnDelete();

            // The spotlight that is nominated (polymorphic)
            $table->string('spotlightable_type', 100);
            $table->unsignedBigInteger('spotlightable_id');
            $table->index(['spotlightable_type', 'spotlightable_id'], 'spotlight_nominees_poly_index');

            // Owner user (denormalized for quick access)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Cached vote counts (updated on each vote to avoid COUNT queries on leaderboard)
            $table->unsignedInteger('free_vote_count')->default(0)
                  ->comment('Community free votes (1 per user per nominee)');
            $table->unsignedInteger('paid_vote_count')->default(0)
                  ->comment('Total approved purchased votes (max 100 per week)');
            $table->unsignedInteger('total_vote_count')->default(0)
                  ->comment('free_vote_count + paid_vote_count');

            // Final result after voting closes
            $table->unsignedTinyInteger('rank')->nullable()
                  ->comment('Final rank (1 = winner). Set when voting closes.');
            $table->boolean('is_winner')->default(false);

            $table->timestamps();

            // A spotlight can only be a nominee once per week
            $table->unique(
                ['spotlight_week_id', 'spotlightable_type', 'spotlightable_id'],
                'spotlight_nominees_unique_per_week'
            );

            // Indexes for leaderboard ordering
            $table->index(['spotlight_week_id', 'total_vote_count'], 'nominees_leaderboard_index');
            $table->index(['spotlight_week_id', 'is_winner']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotlight_week_nominees');
    }
};
