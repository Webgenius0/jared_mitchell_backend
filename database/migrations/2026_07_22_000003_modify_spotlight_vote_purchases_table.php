<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a FK to the new spotlight_vote_packages table, Stripe payment
     * tracking fields, and a 'paid_at' timestamp.
     *
     * The package ENUM and column is kept for backward compatibility;
     * new records will use the package_id FK instead.
     */
    public function up(): void
    {
        Schema::table('spotlight_vote_purchases', function (Blueprint $table) {
            // FK to the new dynamic packages table (nullable for existing rows)
            $table->foreignId('spotlight_vote_package_id')
                ->after('user_id')
                ->nullable()
                ->constrained('spotlight_vote_packages')
                ->nullOnDelete();

            // Stripe payment tracking
            $table->string('stripe_checkout_session_id', 255)
                ->after('order_id')
                ->nullable()
                ->index();

            $table->string('stripe_payment_intent_id', 255)
                ->after('stripe_checkout_session_id')
                ->nullable()
                ->index();

            // Paid-at timestamp (when webhook confirms payment)
            $table->timestamp('paid_at')
                ->after('approved_at')
                ->nullable();
        });

        // Modify the status ENUM to include 'approved' and 'cancelled'
        DB::statement("ALTER TABLE spotlight_vote_purchases MODIFY COLUMN status ENUM('pending', 'approved', 'paid', 'refunded', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original ENUM
        DB::statement("ALTER TABLE spotlight_vote_purchases MODIFY COLUMN status ENUM('pending', 'completed', 'refunded') NOT NULL DEFAULT 'pending'");

        Schema::table('spotlight_vote_purchases', function (Blueprint $table) {
            $table->dropForeign(['spotlight_vote_package_id']);
            $table->dropColumn([
                'spotlight_vote_package_id',
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'paid_at',
            ]);
        });
    }
};
