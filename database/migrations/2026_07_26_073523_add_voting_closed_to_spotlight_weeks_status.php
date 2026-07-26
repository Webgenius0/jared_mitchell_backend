<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add 'voting_closed' to the status ENUM for spotlight_weeks.
     * The original ENUM was: pending, nominating, voting, completed, cancelled
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE spotlight_weeks MODIFY COLUMN status ENUM('pending', 'nominating', 'voting', 'voting_closed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE spotlight_weeks MODIFY COLUMN status ENUM('pending', 'nominating', 'voting', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
