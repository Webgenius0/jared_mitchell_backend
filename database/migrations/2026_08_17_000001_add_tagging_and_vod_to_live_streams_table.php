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
        Schema::table('live_streams', function (Blueprint $table) {
            $table->enum('tag_type', ['general', 'event', 'artist', 'business'])->default('general')->after('playback_url');
            $table->string('streamable_type')->nullable()->after('tag_type');
            $table->unsignedBigInteger('streamable_id')->nullable()->after('streamable_type');
            $table->string('vod_url')->nullable()->after('status');

            $table->index(['streamable_type', 'streamable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_streams', function (Blueprint $table) {
            $table->dropIndex(['streamable_type', 'streamable_id']);
            $table->dropColumn(['tag_type', 'streamable_type', 'streamable_id', 'vod_url']);
        });
    }
};
