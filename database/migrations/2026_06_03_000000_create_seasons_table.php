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
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();

            // Core Identity
            $table->string('contest_type', 50)->default('business')->comment('business, artist, startup, etc.');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // Status Machine: draft → open → applications_closed → in_progress → completed → archived
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
