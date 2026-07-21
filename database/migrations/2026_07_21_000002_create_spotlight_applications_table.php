<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Spotlight Applications — spotlight owners apply to a specific week.
     * Admin/AI selects Top 12 from pending applications.
     */
    public function up(): void
    {
        Schema::create('spotlight_applications', function (Blueprint $table) {
            $table->id();

            // Which week this application is for
            $table->foreignId('spotlight_week_id')
                  ->constrained('spotlight_weeks')
                  ->cascadeOnDelete();

            // Which spotlight is applying (polymorphic: ArtistSpotlight or BusinessSpotlight)
            $table->string('spotlightable_type', 100);
            $table->unsignedBigInteger('spotlightable_id');
            $table->index(['spotlightable_type', 'spotlightable_id'], 'spotlight_apps_poly_index');

            // The user who submitted the application (spotlight owner)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Application status
            // pending   = submitted, awaiting admin review
            // selected  = chosen as a Top 12 nominee
            // rejected  = not chosen for this week (can re-apply next week)
            // withdrawn = owner withdrew their application
            $table->enum('status', ['pending', 'selected', 'rejected', 'withdrawn'])
                  ->default('pending')
                  ->index();

            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->text('reviewer_notes')->nullable();

            $table->timestamps();

            // One spotlight can only apply once per week
            $table->unique(
                ['spotlight_week_id', 'spotlightable_type', 'spotlightable_id'],
                'spotlight_apps_unique_per_week'
            );

            // Indexes for common queries
            $table->index(['spotlight_week_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotlight_applications');
    }
};
