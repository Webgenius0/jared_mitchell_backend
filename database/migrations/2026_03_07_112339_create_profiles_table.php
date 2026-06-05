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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->string('username')->unique();
            $table->string('slug')->unique();

            $table->text('biography')->nullable();
            $table->string('tagline')->nullable();
            $table->string('address')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover')->nullable();

            $table->string('stripe_account_id')->nullable();
            $table->timestamp('stripe_onboarded_at')->nullable();

            // Custom Profile Columns
            $table->text('business_description')->nullable();
            $table->string('website_link')->nullable();
            $table->json('social_links')->nullable();

            // last activity tracking
            $table->timestamp('last_active_at')->nullable();
            $table->boolean('is_online')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
