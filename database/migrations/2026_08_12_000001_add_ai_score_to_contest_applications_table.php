<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contest_applications', function (Blueprint $table) {
            $table->decimal('ai_score', 5, 2)->nullable()->after('status')->comment('AI review score 0.00 - 100.00');
        });

        // Backfill existing applications from their most recent AI review (if any).
        DB::statement("
            UPDATE contest_applications ca
            SET ca.ai_score = (
                SELECT ar.score FROM ai_reviews ar
                WHERE ar.reviewable_type = 'contest_application'
                  AND ar.reviewable_id = ca.id
                  AND ar.score IS NOT NULL
                ORDER BY ar.id DESC
                LIMIT 1
            )
            WHERE ca.ai_score IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contest_applications', function (Blueprint $table) {
            $table->dropColumn('ai_score');
        });
    }
};
