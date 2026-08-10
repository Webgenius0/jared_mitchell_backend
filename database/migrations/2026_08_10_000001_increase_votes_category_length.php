<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen votes.category so rounds with long category names (e.g. lorem-ipsum
     * test categories of 100+ chars) can be voted on without a
     * "Data too long for column 'category'" error.
     */
    public function up(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->string('category', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->change();
        });
    }
};
