<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            // Keep product_id nullable so orders aren't broken if product is deleted
            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Snapshot of product data at time of order
            $table->string('product_name');
            $table->decimal('product_price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('product_thumbnail')->nullable();

            $table->integer('quantity');
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
