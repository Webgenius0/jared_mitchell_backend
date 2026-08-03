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
        Schema::table('spotlight_applications', function (Blueprint $table) {
            $table->decimal('ai_score', 5, 2)->nullable()->after('status')->comment('AI review score 0.00 - 100.00');
            $table->timestamp('ai_reviewed_at')->nullable()->after('ai_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spotlight_applications', function (Blueprint $table) {
            $table->dropColumn(['ai_score', 'ai_reviewed_at']);
        });
    }
};
