<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Spotlight Vote Packages — admin-defined pricing for paid votes.
     * Replaces the old hardcoded PACKAGES constant in the model.
     *
     * Admin can create/edit/activate/deactivate packages from the admin panel.
     */
    public function up(): void
    {
        Schema::create('spotlight_vote_packages', function (Blueprint $table) {
            $table->id();

            // Display / admin-facing name
            $table->string('name')->comment('e.g. Starter, Popular, Boost, Power');

            // Unique slug for API lookup
            $table->string('slug', 60)->unique()->comment('URL-friendly identifier');

            // How many votes this package grants
            $table->unsignedSmallInteger('votes_count');

            // Price in USD
            $table->decimal('price', 8, 2);

            // Optional short description shown to users
            $table->string('description', 255)->nullable();

            // Whether this package is available for purchase
            $table->boolean('is_active')->default(true);

            // Sort order for listing
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            // Common filters
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotlight_vote_packages');
    }
};
