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
        Schema::table('settings', function (Blueprint $table) {
            $table->dateTime('boss_beginnings_start_date')->nullable()->after('time_format');
            $table->dateTime('spotlight_start_date')->nullable()->after('boss_beginnings_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['boss_beginnings_start_date', 'spotlight_start_date']);
        });
    }
};
