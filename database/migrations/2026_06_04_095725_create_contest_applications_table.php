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
        Schema::create('contest_applications', function (Blueprint $table) {
            $table->id();

            // Contestant entity (only businesses for now)
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();

            // Season being applied to
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();

            // Status: pending → needs_review → approved/rejected
            $table->string('status', 30)->default('pending');

            // AI Review fields
            $table->timestamp('ai_reviewed_at')->nullable();
            $table->string('ai_verdict', 30)->nullable()->comment('approve, reject, needs_review');
            $table->decimal('ai_confidence', 5, 2)->nullable();

            // Admin actions
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->text('rejected_reason')->nullable();

            // Flexible
            $table->json('metadata')->nullable();

            $table->timestamps();

            // A business can only apply once (per season — unique handled by season_id + business_id)
            $table->unique(['business_id', 'season_id'], 'application_unique_per_season');

            // Indexes for common queries
            $table->index(['season_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contest_applications');
    }
};
