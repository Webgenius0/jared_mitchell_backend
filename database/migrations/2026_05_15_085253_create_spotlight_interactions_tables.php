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
        // Business Spotlight Likes
        Schema::create('business_spotlight_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('business_spotlight_id')->constrained('business_spotlights')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'business_spotlight_id'], 'bs_likes_user_unique');
        });

        // Business Spotlight Bookmarks
        Schema::create('business_spotlight_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('business_spotlight_id')->constrained('business_spotlights')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'business_spotlight_id'], 'bs_bookmarks_user_unique');
        });

        // Business Spotlight Shares
        Schema::create('business_spotlight_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('business_spotlight_id')->constrained('business_spotlights')->onDelete('cascade');
            $table->string('platform')->nullable();
            $table->timestamps();
        });

        // Artist Spotlight Likes
        Schema::create('artist_spotlight_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('artist_spotlight_id')->constrained('artist_spotlights')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'artist_spotlight_id'], 'as_likes_user_unique');
        });

        // Artist Spotlight Bookmarks
        Schema::create('artist_spotlight_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('artist_spotlight_id')->constrained('artist_spotlights')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'artist_spotlight_id'], 'as_bookmarks_user_unique');
        });

        // Artist Spotlight Shares
        Schema::create('artist_spotlight_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('artist_spotlight_id')->constrained('artist_spotlights')->onDelete('cascade');
            $table->string('platform')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_spotlight_likes');
        Schema::dropIfExists('business_spotlight_bookmarks');
        Schema::dropIfExists('business_spotlight_shares');
        Schema::dropIfExists('artist_spotlight_likes');
        Schema::dropIfExists('artist_spotlight_bookmarks');
        Schema::dropIfExists('artist_spotlight_shares');
    }
};
