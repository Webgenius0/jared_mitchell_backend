<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ── 1. Preserve existing score data into contestant metadata ──
        // Before dropping the columns, save legacy scores into each
        // business's contestant metadata for historical reference.
        if (Schema::hasTable('contestants') && Schema::hasTable('contest_applications')) {
            DB::table('businesses')
                ->orderBy('id')
                ->chunk(100, function ($businesses) {
                    foreach ($businesses as $business) {
                        // Find approved contest applications for this business
                        $applications = DB::table('contest_applications')
                            ->where('contestable_type', 'App\\Models\\Business')
                            ->where('contestable_id', $business->id)
                            ->where('status', 'approved')
                            ->get();

                        foreach ($applications as $app) {
                            // Find the corresponding contestant
                            $contestant = DB::table('contestants')
                                ->where('season_id', $app->season_id)
                                ->where('contestable_type', 'App\\Models\\Business')
                                ->where('contestable_id', $business->id)
                                ->first();

                            if ($contestant) {
                                $existingMetadata = json_decode($contestant->metadata ?? '{}', true);
                                $existingMetadata['legacy_scores'] = [
                                    'total_claps'  => (int) ($business->total_claps ?? 0),
                                    'total_saves'  => (int) ($business->total_saves ?? 0),
                                    'total_shares' => (int) ($business->total_shares ?? 0),
                                    'total_points' => (int) ($business->total_points ?? 0),
                                    'migrated_from_business' => true,
                                    'migrated_at'  => now()->toIso8601String(),
                                ];

                                DB::table('contestants')
                                    ->where('id', $contestant->id)
                                    ->update([
                                        'total_score' => $business->total_points ?? 0,
                                        'metadata'    => json_encode($existingMetadata),
                                    ]);
                            }
                        }


                    }
                });
        }

        // ── 2. Drop the competition columns from businesses table ─────
        Schema::table('businesses', function (Blueprint $table) {
            // Only drop columns that exist (safe for repeatable migrations)
            $columns = [
                'total_claps',
                'total_saves',
                'total_shares',
                'total_points',
                'is_featured',
                'owner_name',
            ];

            $existingColumns = array_intersect(
                $columns,
                Schema::getColumnListing('businesses')
            );

            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });

        Log::info('Migration: Removed competition fields from businesses table. '
            . 'Legacy scores preserved in contestant metadata and business metadata.');
    }

    /**
     * Reverse the migrations.
     *
     * Re-adds the dropped columns with default values.
     * NOTE: This restores the schema but NOT the data.
     * Legacy scores remain in contestant metadata.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Re-add the columns with defaults
            $table->unsignedBigInteger('total_claps')->default(0);
            $table->unsignedBigInteger('total_saves')->default(0);
            $table->unsignedBigInteger('total_shares')->default(0);
            $table->unsignedBigInteger('total_points')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('owner_name')->nullable();
        });

        Log::info('Migration rollback: Re-added competition fields to businesses table. '
            . 'Data was NOT restored from contestant metadata. Run a data recovery script if needed.');
    }
};
