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
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique();       // e.g. "OSI-20250322-00042"

            // Event & Ticket
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('ticket_tier_id')
                  ->nullable()
                  ->constrained('event_ticket_tiers')
                  ->nullOnDelete();

            // Attendee Information (Section from screenshot)
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone_number')->nullable();

            // Optional: link to a registered user account
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Order Summary
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->decimal('unit_price', 8, 2)->unsigned()->default(0.00);
            $table->decimal('service_fee', 8, 2)->unsigned()->default(0.00);
            $table->decimal('subtotal', 8, 2)->unsigned()->default(0.00);
            $table->decimal('total', 8, 2)->unsigned()->default(0.00);
            $table->string('currency', 3)->default('USD');

            // Payment
            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'refunded',
                'free',
            ])->default('pending');

            $table->string('payment_intent_id')->nullable(); // Stripe PaymentIntent or similar
            $table->string('payment_method')->nullable(); // e.g. "card", "paypal"
            $table->timestamp('paid_at')->nullable();

            // Registration Status
            $table->enum('status', [
                'pending',
                'confirmed',
                'cancelled',
                'attended',
                'no_show',
            ])->default('pending');

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Check-in
            $table->string('qr_code')->nullable()->unique();
            $table->timestamp('checked_in_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('booking_reference');
            $table->index(['event_id', 'status']);
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
