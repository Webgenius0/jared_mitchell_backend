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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();

            // Owner
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Category
            $table->foreignId('business_category_id')->constrained()->restrictOnDelete();

            // Basic Information
            $table->string('owner_name')->nullable();
            $table->string('business_name')->nullable();
            $table->string('slug')->unique();

            $table->year('year_founded')->nullable();

            $table->string('website')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();

            // Additional Information
            $table->text('description')->nullable();
            $table->string('logo')->nullable();

            // Competition Status
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');

            $table->boolean('is_featured')->default(false);

            $table->unsignedBigInteger('total_claps')->default(0);
            $table->unsignedBigInteger('total_saves')->default(0);
            $table->unsignedBigInteger('total_shares')->default(0);
            $table->unsignedBigInteger('total_points')->default(0);

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
