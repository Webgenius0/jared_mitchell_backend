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
        Schema::create('artist_spotlights', function (Blueprint $table) {
            $table->id();
            // Step 1 · Artist Identification
            $table->string('full_legal_name');
            $table->string('artist_stage_name');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->date('date_of_birth');                  // Must be 18+
            $table->string('city');
            $table->string('state');

            // Social Media Handles (Step 1)
            $table->string('instagram_handle')->nullable();
            $table->string('tiktok_handle')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('website_portfolio_url')->nullable();

            // Step 2 · Artist Category
            $table->foreignId('artist_category_id')
                  ->nullable()
                  ->constrained('artist_categories')
                  ->nullOnDelete();
            $table->string('category_other_description')->nullable(); // filled when category = "Other"

            // Step 3 · Artist Story
            // Short Bio (2-4 sentences) — shown on spotlight card
            $table->text('short_bio')->nullable();                    // max 500 chars

            // Full Artist Story (5-20 sentences) — main spotlight page
            $table->longText('full_artist_story')->nullable();        // max 5000 chars

            // Why Should Your Story Be Spotlighted? (3-6 sentences)
            $table->text('why_spotlighted')->nullable();              // max 2000 chars

            // What Message Do You Want to Share with the Community?
            $table->text('community_message')->nullable();            // used for "pull quote" Step

            // What Are Your Current Goals as an Artist?
            $table->text('current_goals')->nullable();                // used for "what's next" Step

            // Step 4 · Media Uploads
            // Professional Headshot / Portrait (required, 1 file)
            $table->string('headshot_path')->nullable();              // JPG, PNG, HEIC – max 150 MB

            // Photos of Your Art / Work (3-5 photos, required)
            $table->json('artwork_photo_paths')->nullable();          // array of paths, up to 5 files

            // Behind-the-Scenes Photo (optional)
            $table->string('behind_scenes_photo_path')->nullable();   // JPG, PNG, HEIC – max 150 MB

            // Short Intro Video (15-30 seconds, optional)
            $table->string('intro_video_path')->nullable();           // MP4, MOV – max 150 MB

            // Step 5 · Consent & Rights
            // Public Release Agreement
            $table->boolean('consent_public_release')->default(false);
            // Ownership Declaration
            $table->boolean('consent_ownership_declaration')->default(false);
            // Interview Permission
            $table->boolean('consent_interview_permission')->default(false);

            // Step 6 · Optional Information
            $table->string('talent_manager_contact')->nullable();     // name and email
            $table->string('agent_contact')->nullable();              // name and email
            $table->string('press_kit_url')->nullable();
            $table->text('previous_interviews')->nullable();          // comma-separated or JSON links
            $table->text('awards_recognition')->nullable();
            $table->string('preferred_pronouns')->nullable();
            $table->string('preferred_contact_method')->nullable();
            $table->text('interview_availability')->nullable();       // free-text availability notes

            // Submission Tracking
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'featured',
            ])->default('draft');

            $table->unsignedTinyInteger('current_step')->default(1); // 1–6
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->text('reviewer_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('email');
            $table->index('status');
            $table->index('artist_category_id');
            $table->index('date_of_birth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artist_spotlights');
    }
};
