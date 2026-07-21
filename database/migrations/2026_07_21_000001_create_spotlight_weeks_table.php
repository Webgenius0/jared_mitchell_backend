<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Spotlight Weeks — one row per calendar week.
     * Manages both artist and business spotlights through polymorphic relationships.
     * Created automatically by the scheduler (Monday 12:00 AM) or manually by admin.
     */
    public function up(): void
    {
        Schema::create('spotlight_weeks', function (Blueprint $table) {
            $table->id();

            // Calendar identity (ISO week)
            $table->unsignedSmallInteger('week_number')->comment('ISO 8601 week number (1–53)');
            $table->unsignedSmallInteger('year');

            // Lifecycle status
            $table->enum('status', ['pending', 'nominating', 'voting', 'completed', 'cancelled'])->default('pending')->index();

            // Voting window: Monday 12:00 AM → Sunday 11:59 PM
            $table->timestamp('voting_starts_at')->nullable();
            $table->timestamp('voting_ends_at')->nullable();

            // Winner (polymorphic to ArtistSpotlight or BusinessSpotlight)
            $table->string('winner_spotlightable_type', 100)->nullable();
            $table->unsignedBigInteger('winner_spotlightable_id')->nullable();
            $table->timestamp('announced_at')->nullable();

            // Flexible metadata (e.g. AI scoring notes, admin remarks)
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Only one week per calendar week/year (manages both artist + business)
            $table->unique(['week_number', 'year'], 'spotlight_weeks_unique_per_week');

            // Index for common queries
            $table->index(['voting_starts_at', 'voting_ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotlight_weeks');
    }
};
