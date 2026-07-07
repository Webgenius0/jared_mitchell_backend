<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();

            // Scope
            $table->foreignId('season_id')
                  ->constrained('seasons')
                  ->cascadeOnDelete();
            $table->foreignId('round_id')
                  ->nullable()
                  ->constrained('rounds')
                  ->nullOnDelete()
                  ->comment('Null for overall leaderboard across all rounds');

            // The contestant
            $table->foreignId('contestant_id')
                  ->constrained('contestants')
                  ->cascadeOnDelete();

            // Leaderboard data
            $table->unsignedInteger('rank')->default(0);
            $table->decimal('total_score', 10, 2)->default(0);
            $table->unsignedInteger('votes_count')->default(0);
            $table->decimal('avg_score', 5, 2)->nullable()
                  ->comment('Average score across all votes received');

            // Snapshot of contestant data at calculation time (avoids N+1 on page load)
            $table->json('snapshot')->nullable()
                  ->comment('Denormalized contestant name, avatar, business name for fast rendering');

            // Timing
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            // One entry per contestant per round + one for overall
            $table->unique(['round_id', 'contestant_id'], 'unique_leaderboard_entry_per_round');
            $table->unique(['season_id', 'contestant_id', 'round_id'], 'unique_leaderboard_entry');

            // Indexes for quick leaderboard rendering
            $table->index(['round_id', 'rank']);
            $table->index(['season_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
    }
};
