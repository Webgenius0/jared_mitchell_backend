<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change tag_type from ENUM to string/varchar to prevent truncation for new tag types
        Schema::table('live_streams', function (Blueprint $table) {
            $table->string('tag_type', 50)->default('boss_beginning')->change();
        });

        // Update any existing 'general' tags to 'boss_beginning'
        DB::table('live_streams')->where('tag_type', 'general')->update(['tag_type' => 'boss_beginning']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_streams', function (Blueprint $table) {
            $table->string('tag_type', 50)->default('general')->change();
        });
    }
};
