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
        Schema::create('season_sponsor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')
                ->constrained('seasons')
                ->cascadeOnDelete();
            $table->foreignId('sponsor_id')
                ->constrained('sponsors')
                ->cascadeOnDelete();
            $table->unique('season_id', 'season_sponsor_season_unique'); // one sponsor per season
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('season_sponsor');
    }
};
