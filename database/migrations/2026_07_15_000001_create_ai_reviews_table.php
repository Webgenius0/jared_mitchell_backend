<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_reviews', function (Blueprint $table) {
            $table->id();

            // Polymorphic reviewable (contest_applications, later round_submissions)
            $table->string('reviewable_type', 100);
            $table->unsignedBigInteger('reviewable_id');
            $table->index(['reviewable_type', 'reviewable_id'], 'ai_reviews_poly_index');

            // Provider & Model
            $table->string('provider', 50)->comment('openai, anthropic, gemini');
            $table->string('model', 100);

            // Scoring
            $table->decimal('score', 5, 2)->nullable()->comment('0.00 - 100.00');
            $table->string('verdict', 50)->nullable()->comment('approve, reject, needs_review');
            $table->decimal('confidence', 5, 2)->nullable()->comment('0.00 - 1.00');

            // Full response data
            $table->json('raw_response')->nullable()->comment('Complete AI output for audit');
            $table->json('parsed_result')->nullable()->comment('Structured: score, verdict, reasoning, strengths, weaknesses');
            $table->text('review_notes')->nullable();

            // Token tracking (cost monitoring)
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->integer('total_tokens')->nullable();

            // Timing
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['reviewable_type', 'reviewable_id', 'verdict']);
            $table->index('confidence');
            $table->index('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_reviews');
    }
};
