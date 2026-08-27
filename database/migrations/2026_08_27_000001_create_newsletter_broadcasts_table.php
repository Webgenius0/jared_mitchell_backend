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
        Schema::create('newsletter_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('template_style')->default('spotlight'); // spotlight, contest, educational, promotional
            $table->string('primary_color')->default('#6366f1');
            $table->string('banner_image_url')->nullable();
            $table->string('cta_button_text')->nullable();
            $table->string('cta_button_url')->nullable();
            $table->string('topic_type')->default('general');
            $table->text('ai_prompt')->nullable();
            $table->longText('html_content');
            $table->integer('total_subscribers')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->enum('status', ['draft', 'processing', 'completed', 'failed'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_broadcasts');
    }
};
