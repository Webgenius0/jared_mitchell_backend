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
        Schema::create('comparison_features', function (Blueprint $table) {
            $table->id();
 
            $table->string('feature_name', 150);         // Row label, e.g. "AI Market Insights"
            $table->string('category', 100)->nullable();  // Optional group header, e.g. "Event & Community"
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparison_features');
    }
};
