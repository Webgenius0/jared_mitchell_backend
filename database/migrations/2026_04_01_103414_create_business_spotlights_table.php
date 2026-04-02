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
        Schema::create('business_spotlights', function (Blueprint $table) {
            $table->id();
            // ─────────────────────────────────────────────
            // Step 1 – Business Information
            // ─────────────────────────────────────────────
            $table->string('business_name');
            $table->string('owner_founder_name');
            $table->string('business_category');
            $table->year('year_founded')->nullable();
            $table->string('business_website')->nullable();
            $table->string('city');
            $table->string('state');
 
            // ─────────────────────────────────────────────
            // Step 2 – Business Story
            // ─────────────────────────────────────────────
            $table->text('business_story')->nullable();                  // Tell us your business story (max 500 chars)
            $table->text('products_services')->nullable();               // What products or services do you offer? (max 1000 chars)
            $table->text('challenges_overcome')->nullable();             // What challenges has your business overcome? (max 1000 chars)
            $table->text('unique_factor')->nullable();                   // What makes your business unique?
            $table->text('target_customer')->nullable();                 // Who is your target customer?
 
            // ─────────────────────────────────────────────
            // Step 3 – Contact Information
            // ─────────────────────────────────────────────
            $table->string('email');
            $table->string('phone_number')->nullable();
            $table->string('best_contact_time')->nullable();             // e.g. Morning, Afternoon, Evening
 
            // Social Media Links
            $table->string('instagram_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('google_business_profile_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('fanbase_url')->nullable();
 
            // ─────────────────────────────────────────────
            // Step 4 – Images (store file paths / S3 keys)
            // ─────────────────────────────────────────────
            $table->string('portrait_photo_path')->nullable();           // Business Owner Portrait (required on form)
            $table->string('storefront_workspace_photo_path')->nullable(); // Storefront / Workspace Photo (required on form)
            $table->json('product_service_photo_paths')->nullable();     // Product or Service Photos (multiple)
            $table->string('team_photo_path')->nullable();               // Team Photo (optional)
 
            // ─────────────────────────────────────────────
            // Step 5 – Service Details
            // ─────────────────────────────────────────────
            $table->enum('service_type', [
                'in_person_only',
                'online_only',
                'both_in_person_and_online',
            ])->default('both_in_person_and_online');
 
            // ─────────────────────────────────────────────
            // Step 6 – Spotlight Consideration
            // ─────────────────────────────────────────────
            $table->text('why_featured')->nullable();                    // Why do you want your business featured? (max 500 chars)
            $table->text('growth_vision')->nullable();                   // How would a spotlight help your business grow? (max 500 chars)
 
            // Permissions (checkboxes)
            $table->boolean('permission_feature_on_osi')->default(false);       // Feature my business on OSI
            $table->boolean('permission_use_submitted_photos')->default(false);  // Use submitted photos on OSI channels
            $table->boolean('permission_share_business_story')->default(false);  // Share business story on OSI channels
 
            // ─────────────────────────────────────────────
            // Submission tracking
            // ─────────────────────────────────────────────
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'rejected',
            ])->default('draft');
 
            $table->unsignedTinyInteger('current_step')->default(1);     // Track which step the user is on (1–6)
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reviewer_notes')->nullable();
 
            $table->timestamps();
            $table->softDeletes();
 
            // ─────────────────────────────────────────────
            // Indexes
            // ─────────────────────────────────────────────
            $table->index('email');
            $table->index('status');
            $table->index('business_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_spotlights');
    }
};
