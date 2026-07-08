<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('round_transitions', function (Blueprint $table) {
            $table->id();

            // Which round this transition came from
            $table->foreignId('from_round_id')->constrained('rounds')->cascadeOnDelete();

            // Which round contestants advanced to (null if season ended)
            $table->foreignId('to_round_id')->nullable()->constrained('rounds')->nullOnDelete();

            // Season context
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();

            // Transition data
            $table->string('status', 30)->default('pending')->comment('pending, processing, completed, failed');
            $table->string('elimination_rule', 50)->comment('Which rule was applied (snapshot at transition time)');
            $table->json('transition_config')->nullable()->comment('Snapshot of the round advancement_config at transition time');

            // Counts
            $table->unsignedInteger('total_contestants')->default(0);
            $table->unsignedInteger('advanced_count')->default(0);
            $table->unsignedInteger('eliminated_count')->default(0);

            // Results
            $table->json('advanced_contestants')->nullable()->comment('Array of {id, display_name, rank, score}');
            $table->json('eliminated_contestants')->nullable()->comment('Array of {id, display_name, rank, score}');
            $table->json('metadata')->nullable()->comment('Errors, warnings, or extra processing data');

            // Timing
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['from_round_id', 'status']);
            $table->index(['season_id', 'status']);
            $table->index('processed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('round_transitions');
    }
};
