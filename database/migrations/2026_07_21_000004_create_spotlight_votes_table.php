<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Spotlight Votes — free community votes.
     * Each user can cast exactly 1 vote per nominee per week.
     * Toggle behavior: voting again removes the vote.
     */
    public function up(): void
    {
        Schema::create('spotlight_votes', function (Blueprint $table) {
            $table->id();

            // The nominee being voted for
            $table->foreignId('spotlight_week_nominee_id')
                  ->constrained('spotlight_week_nominees')
                  ->cascadeOnDelete();

            // Who voted
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();

            // One vote per user per nominee (enforces single-vote rule)
            $table->unique(
                ['spotlight_week_nominee_id', 'user_id'],
                'spotlight_votes_unique_per_user'
            );

            // Index for checking if user has voted
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotlight_votes');
    }
};
