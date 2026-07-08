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
        // 1. Create the contestants table
        Schema::create('contestants', function (Blueprint $table) {
            $table->id();

            // Season
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();

            // Polymorphic contestable (Business, User, etc.)
            $table->string('contestable_type', 100);
            $table->unsignedBigInteger('contestable_id');
            $table->index(['contestable_type', 'contestable_id'], 'contestants_poly_index');

            // Contest identity (denormalized for leaderboard performance)
            $table->string('display_name');
            $table->string('slug')->nullable();
            $table->string('avatar_url')->nullable();

            // Status: active, eliminated, disqualified, withdrawn, winner, runner_up, finalist
            $table->string('status', 30)->default('active');

            // Aggregate score (used for season-level ranking)
            $table->decimal('total_score', 12, 2)->default(0);

            // Round tracking
            $table->foreignId('current_round_id')->nullable()->constrained('rounds')->nullOnDelete();
            $table->foreignId('eliminated_in_round_id')->nullable()->constrained('rounds')->nullOnDelete();

            // Timeline
            $table->timestamp('entered_at')->nullable();
            $table->timestamp('eliminated_at')->nullable();

            // Flexible metadata (season-specific profile info)
            $table->json('metadata')->nullable();

            $table->timestamps();

            // A contestable entity can only be a contestant once per season
            $table->unique(['contestable_type', 'contestable_id', 'season_id'],'contestant_unique_per_season');

            // Indexes for common queries
            $table->index(['season_id', 'status']);
            $table->index(['season_id', 'current_round_id']);
            $table->index('total_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contestants');
    }
};
