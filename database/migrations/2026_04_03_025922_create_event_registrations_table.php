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

            // Registration
            $table->string('booking_reference')->unique(); // EVT-20260709-000001
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'attended', 'no_show'])->default('pending');


            // Relationships
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_tier_id')->nullable()->constrained('event_ticket_tiers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();


            // Attendee Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone_number')->nullable();


            // Ticket Summary
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');


            // Stripe Checkout Session
            $table->string('stripe_checkout_session_id')->nullable()->unique();

            // Stripe Payment Intent
            $table->string('stripe_payment_intent_id')->nullable()->unique();

            // Stripe Customer
            $table->string('stripe_customer_id')->nullable();

            // Stripe Charge (Available after successful payment)
            $table->string('stripe_charge_id')->nullable();

            // Stripe Refund
            $table->string('stripe_refund_id')->nullable();

            $table->enum('payment_status', ['pending', 'processing', 'paid', 'failed', 'refunded', 'free'])->default('pending');


            //Timeline
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('failed_at')->nullable();


            // QR Check-in
            $table->string('qr_code')->nullable()->unique();

            // Notes
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();


            // Indexes
            $table->index('booking_reference');
            $table->index('email');
            $table->index(['event_id', 'status']);
            $table->index('payment_status');
            $table->index('stripe_payment_intent_id');
            $table->index('stripe_customer_id');
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
