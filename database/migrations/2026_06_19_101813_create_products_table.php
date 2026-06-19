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

            // Basic Product Info
            $table->string('name');
            $table->string('slug')->unique();

            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();

            // Inventory
            $table->integer('stock')->default(0);
            $table->boolean('track_stock')->default(true);

            // Product Type
            $table->enum('type', [
                'physical',
                'digital',
                'service'
            ])->default('physical');

            // Brand
            $table->string('brand')->nullable();

            // Product Thumbnail
            $table->string('thumbnail')->nullable();

            // Vendor Information
            $table->string('vendor_name')->nullable();
            $table->string('vendor_email')->nullable();
            $table->string('vendor_phone')->nullable();
            $table->text('vendor_address')->nullable();
            $table->longText('vendor_details')->nullable();

            // category id
            $table->foreignId('category_id')->nullable()->references('id')->on('product_categories')->onDelete('cascade');

            // Status
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
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
