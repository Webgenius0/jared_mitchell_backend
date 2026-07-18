<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('round_submissions', function (Blueprint $table) {
            $table->id();

            // Who submitted
            $table->foreignId('contestant_id')->constrained('contestants')->cascadeOnDelete();

            // Which round
            $table->foreignId('round_id')->constrained('rounds')->cascadeOnDelete();

            // Submission content
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->json('media_urls')->nullable()->comment('Array of uploaded file paths/URLs');

            // Status & scoring
            $table->string('status', 30)->default('draft')->comment('draft, submitted, approved, rejected');
            $table->decimal('score', 5, 2)->nullable()->comment('AI or judge score 0-100');
            $table->timestamp('submitted_at')->nullable();

            // Flexible
            $table->json('metadata')->nullable();

            $table->timestamps();

            // A contestant can only have one submission per round
            $table->unique(['contestant_id', 'round_id'], 'unique_submission_per_round');

            // Indexes for common queries
            $table->index(['round_id', 'status']);
            $table->index(['contestant_id', 'status']);
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('round_submissions');
    }
};
