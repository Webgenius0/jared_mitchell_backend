<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();

            // Season FK — replaces round_session_id
            $table->foreignId('season_id')
                ->constrained()
                ->cascadeOnDelete();

            // Identity
            $table->integer('round_number');
            $table->string('title');
            $table->text('goal')->nullable();
            $table->longText('requirements')->nullable();

            // Competition mechanics
            $table->string('voting_strategy', 50)->default('popular_vote')->comment('popular_vote, judge_scored, weighted, admin_pick, single_elimination');
            $table->string('submission_type', 50)->default('multi')->comment('file_upload, video, link, text, multi');
            $table->json('submission_requirements')->nullable()->comment('Structured requirements: { video: { required: true, max_duration_sec: 180 } }');
            $table->integer('advance_limit')->nullable();
            $table->string('elimination_rule', 50)->default('advance_limit')->comment('bottom_n, top_percent, score_below_threshold, advance_limit, all_advance, single_elimination, admin_pick');
            $table->json('advancement_config')->nullable()->comment('{ top_n: 10, threshold: 80, percent: 25, tiebreakers: [...] }');

            // State
            $table->boolean('is_active')->default(false);

            // Timeline
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('voting_ends_at')->nullable()->comment('Voting may extend beyond the submission deadline');

            // Ordering & metadata
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();

            $table->timestamps();

            // A season cannot have duplicate round numbers
            $table->unique(['season_id', 'round_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
