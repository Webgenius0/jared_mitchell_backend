<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();

            // Owner
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Basic Information
            $table->string('owner_name')->nullable();
            $table->string('business_name')->nullable();
            $table->string('slug')->unique();
            $table->string('owner_founder_name')->nullable();
            $table->longText('story')->nullable();
            $table->longText('mission')->nullable();
            $table->longText('website_social_media')->nullable();
            $table->longText('community_impact_statement')->nullable();
            $table->longText('revenue_stage')->nullable();
            $table->longText('why_they_deserve_to_compete')->nullable();
            $table->string('photo_video')->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->boolean('is_featured')->default(false);

            // Competition Status
            $table->unsignedBigInteger('total_claps')->default(0);
            $table->unsignedBigInteger('total_saves')->default(0);
            $table->unsignedBigInteger('total_shares')->default(0);
            $table->unsignedBigInteger('total_points')->default(0);
            // New fields for Business CRUD API

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
