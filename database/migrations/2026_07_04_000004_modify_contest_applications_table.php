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
        // ── 1. Add all new columns to contest_applications ────────────
        Schema::table('contest_applications', function (Blueprint $table) {

            // Season FK — replaces round_session_id
            $table->foreignId('season_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete();

            // Polymorphic contestable — replaces business_id
            $table->string('contestable_type', 100)->nullable()->after('id');
            $table->unsignedBigInteger('contestable_id')->nullable()->after('contestable_type');

            // Change status from ENUM to string(30) for flexibility
            // (ENUMs are painful to alter in MySQL)
            // We drop the ENUM and recreate as string
            // Note: This requires a raw SQL statement for MySQL
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE contest_applications MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'pending'");
            }

            // AI Review fields
            $table->timestamp('ai_reviewed_at')->nullable()->after('approved_at');
            $table->string('ai_verdict', 30)->nullable()->after('ai_reviewed_at')
                  ->comment('approve, reject, needs_review');
            $table->decimal('ai_confidence', 5, 2)->nullable()->after('ai_verdict');

            // Additional fields
            $table->text('rejected_reason')->nullable()->after('admin_note');
            $table->json('metadata')->nullable()->after('rejected_reason');
        });

        // ── 2. Migrate existing data: business_id → polymorphic ──────
        DB::table('contest_applications')
            ->whereNull('contestable_type')
            ->whereNotNull('business_id')
            ->update([
                'contestable_type' => 'App\\Models\\Business',
                'contestable_id'   => DB::raw('business_id'),
            ]);

        // ── 3. Migrate existing data: round_session_id → season_id ───
        if (Schema::hasTable('season_migration_map')) {
            DB::table('contest_applications')
                ->whereNull('season_id')
                ->orderBy('id')
                ->chunk(100, function ($applications) {
                    foreach ($applications as $app) {
                        $mapping = DB::table('season_migration_map')
                            ->where('round_session_id', $app->round_session_id)
                            ->first();

                        if ($mapping) {
                            DB::table('contest_applications')
                                ->where('id', $app->id)
                                ->update(['season_id' => $mapping->season_id]);
                        }
                    }
                });
        }

        // ── 4. Update the unique constraint ──────────────────────────
        // Drop the old unique on business_id
        try {
            Schema::table('contest_applications', function (Blueprint $table) {
                $table->dropUnique(['business_id']);
            });
        } catch (\Exception $e) {
            // Index may be named differently on this DB
            try {
                Schema::table('contest_applications', function (Blueprint $table) {
                    $table->dropUnique('contest_applications_business_id_unique');
                });
            } catch (\Exception $e2) {
                // Ignore
            }
        }

        // Add the new polymorphic unique constraint
        try {
            Schema::table('contest_applications', function (Blueprint $table) {
                $table->unique(['contestable_type', 'contestable_id', 'season_id'],
                               'application_unique_per_season');
            });
        } catch (Exception $e) {
            echo 'Note: Could not add polymorphic unique constraint. Check for duplicate entries.' . PHP_EOL;
        }

        // 5. Add indexes for common queries
        try {
            Schema::table('contest_applications', function (Blueprint $table) {
                $table->index(['season_id', 'status']);
                $table->index(['contestable_type', 'contestable_id']);
            });
        } catch (Exception $e) {
            // Indexes may already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contest_applications', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'season_id',
                'contestable_type',
                'contestable_id',
                'ai_reviewed_at',
                'ai_verdict',
                'ai_confidence',
                'rejected_reason',
                'metadata',
            ]);

            // Drop new indexes
            try {
                $table->dropIndex(['season_id', 'status']);
                $table->dropIndex(['contestable_type', 'contestable_id']);
                $table->dropUnique('application_unique_per_season');
            } catch (Exception $e) {
                // May not exist
            }

            // Restore old unique constraint
            $table->unique('business_id');

            // Restore status ENUM (MySQL only)
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE contest_applications MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'withdrawn') NOT NULL DEFAULT 'pending'");
            }
        });
    }
};
