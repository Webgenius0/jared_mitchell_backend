<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            /*
            |------------------------------------------------------------------
            | GROUP 1 — General / Identity
            |------------------------------------------------------------------
            */
            $table->string('app_name')->nullable();           // "Acme Corp"
            $table->string('app_tagline')->nullable();        // "Build something great"
            $table->string('app_version')->nullable();        // "1.0.0"
            $table->string('author_name')->nullable();        // "John Doe"
            $table->string('author_url')->nullable();         // "https://johndoe.com"
            $table->string('copyright')->nullable();          // "© 2026 Acme Corp."
            $table->text('about')->nullable();                // Short about text

            /*
            |------------------------------------------------------------------
            | GROUP 2 — Branding / Media
            |------------------------------------------------------------------
            */
            $table->string('logo')->nullable();               // Main logo (dark bg)
            $table->string('logo_light')->nullable();         // Logo for dark bg / white version
            $table->string('logo_dark')->nullable();          // Logo for light bg / dark version
            $table->string('logo_sm')->nullable();            // Small / icon version
            $table->unsignedSmallInteger('logo_width')->nullable();
            $table->unsignedSmallInteger('logo_height')->nullable();
            $table->string('favicon')->nullable();
            $table->string('og_image')->nullable();           // Default Open Graph / social share image
            $table->string('thumbnail')->nullable();          // Default thumbnail

            /*
            |------------------------------------------------------------------
            | GROUP 3 — Contact
            |------------------------------------------------------------------
            */
            $table->string('contact_email')->nullable();
            $table->string('support_email')->nullable();
            $table->string('noreply_email')->nullable();
            $table->string('phone_primary')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code')->nullable();
            $table->text('map_embed')->nullable();            // <iframe> embed code

            /*
            |------------------------------------------------------------------
            | GROUP 4 — Social Media
            |------------------------------------------------------------------
            */
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();            // or "x"
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('youtube')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('pinterest')->nullable();
            $table->string('github')->nullable();
            $table->string('telegram')->nullable();
            $table->string('discord')->nullable();

            /*
            |------------------------------------------------------------------
            | GROUP 5 — SEO / Meta
            |------------------------------------------------------------------
            */
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('google_analytics_id')->nullable();   // "G-XXXXXXXXXX"
            $table->string('google_tag_manager_id')->nullable(); // "GTM-XXXXXXX"
            $table->string('google_site_verification')->nullable();
            $table->string('facebook_pixel_id')->nullable();
            $table->text('header_scripts')->nullable();           // Custom <head> scripts
            $table->text('footer_scripts')->nullable();           // Custom </body> scripts

            /*
            |------------------------------------------------------------------
            | GROUP 6 — Mail / SMTP
            |------------------------------------------------------------------
            */
            $table->string('mail_from_name')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->longText('mail_signature')->nullable();       // HTML email signature

            /*
            |------------------------------------------------------------------
            | GROUP 7 — Legal / Compliance
            |------------------------------------------------------------------
            */
            $table->string('privacy_policy_url')->nullable();
            $table->string('terms_url')->nullable();
            $table->string('refund_policy_url')->nullable();
            $table->boolean('cookie_consent_enabled')->default(false);
            $table->text('cookie_consent_message')->nullable();

            /*
            |------------------------------------------------------------------
            | GROUP 8 — System / Maintenance
            |------------------------------------------------------------------
            */
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            $table->string('maintenance_allowed_ips')->nullable();   // comma-separated
            $table->boolean('registration_enabled')->default(true);
            $table->boolean('email_verification_required')->default(false);
            $table->string('timezone')->default('UTC');
            $table->string('date_format')->default('d M Y');
            $table->string('time_format')->default('h:i A');
            $table->string('currency_code')->nullable();             // "USD"
            $table->string('currency_symbol')->nullable();           // "$"
            $table->string('language')->default('en');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
