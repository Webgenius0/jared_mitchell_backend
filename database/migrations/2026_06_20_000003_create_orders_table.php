<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('order_number')->unique();
            $table->string('invoice_number')->nullable()->unique();

            // Pricing
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Statuses
            $table->string('status')->default('pending');
            // pending, confirmed, processing, shipped, delivered, cancelled, refunded

            $table->string('payment_status')->default('unpaid');
            // unpaid, paid, refunded, partially_refunded

            $table->string('payment_method')->nullable();
            $table->string('payment_transaction_id')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();

            // Timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
