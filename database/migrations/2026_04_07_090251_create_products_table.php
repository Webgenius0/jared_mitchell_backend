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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('display_id')->nullable()->unique();

            $table->string('name');
            $table->longText('description')->nullable();
            $table->longText('image')->nullable();

            $table->string('type')->nullable();
            $table->integer('stock')->default(0);

            $table->string('category')->nullable();

            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();

            $table->string('target_audience')->nullable();
            $table->string('delivery_type')->nullable();

            $table->string('sku')->nullable()->unique();
            $table->string('status')->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
