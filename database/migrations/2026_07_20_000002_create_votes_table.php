<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();

            // Who voted
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Which round they're voting in
            $table->foreignId('round_id')->constrained('rounds')->cascadeOnDelete();

            // What they voted for (polymorphic: contestant, business, submission)
            $table->string('votable_type', 100);
            $table->unsignedBigInteger('votable_id');

            // Vote details
            $table->string('vote_type', 30)->default('upvote')->comment('upvote, downvote, score_1_5, score_1_10');
            $table->decimal('weight', 8, 2)->default(1.00)
                  ->comment('Vote weight (1.0 = standard, higher for weighted strategies)');

            // Flexible
            $table->json('metadata')->nullable();

            $table->string('category', 50)->nullable()->comment('e.g. innovation, presentation, impact, quality, growth');

            $table->timestamps();

            // One vote per user per votable entity per round
            $table->unique(['user_id', 'round_id', 'votable_type', 'votable_id', 'category'], 'unique_vote_per_category_per_round');

            // Indexes for vote counting and leaderboard queries
            $table->index(['round_id', 'votable_type', 'votable_id'], 'votes_round_entity_index');
            $table->index(['round_id', 'vote_type']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
