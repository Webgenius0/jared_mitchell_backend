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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            // Core Details
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
 
            // Date & Time
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('timezone')->default('EST');          // e.g. "EST", "CST"
 
            // Location
            $table->string('venue_name')->nullable();            // e.g. "Downtown Gallery"
            $table->string('address')->nullable();
            $table->string('city');
            $table->string('state');
 
            // Host
            $table->string('hosted_by')->nullable();             // e.g. "OSI Team", "Creative Collective"
 
            // Media
            $table->string('cover_image_path')->nullable();      // featured hero image / thumbnail
            $table->string('promo_video_path')->nullable();      // video shown on event card / detail page
 
            // Classification & Badges
            $table->enum('event_type', [
                'featured',
                'workshop',
                'art_exhibition',
                'pop_up',
                'networking',
                'other',
            ])->default('other');
 
            $table->boolean('is_spotlight_eligible')->default(false);
            $table->boolean('is_featured')->default(false);
 
            // Engagement
            $table->unsignedInteger('like_count')->default(0);
 
            // Ticketing
            $table->string('ticket_url')->nullable();            // external ticketing link if applicable
            $table->boolean('tickets_available')->default(true);
 
            // Status
            $table->enum('status', [
                'draft',
                'published',
                'cancelled',
                'completed',
            ])->default('draft');
 
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
 
            $table->timestamps();
            $table->softDeletes();
 
            $table->index('starts_at');
            $table->index('status');
            $table->index('event_type');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
