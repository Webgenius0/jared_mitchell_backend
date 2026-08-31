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
        Schema::create('winner_articles', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('boss_beginning'); // 'boss_beginning' or 'spotlight'
            $table->foreignId('contestant_id')->nullable()->constrained('contestants')->nullOnDelete();
            $table->foreignId('spotlight_week_nominee_id')->nullable()->constrained('spotlight_week_nominees')->nullOnDelete();
            $table->foreignId('season_id')->nullable()->constrained('seasons')->nullOnDelete();
            $table->foreignId('spotlight_week_id')->nullable()->constrained('spotlight_weeks')->nullOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('winner_article_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('winner_article_id')->constrained('winner_articles')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('file_type')->default('image'); // 'image' or 'video'
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winner_article_media');
        Schema::dropIfExists('winner_articles');
    }
};
