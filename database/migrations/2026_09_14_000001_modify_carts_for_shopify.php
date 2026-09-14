<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['product_id']);
        });

        Schema::table('carts', function (Blueprint $table) {
            // Add variant_id if missing
            if (!Schema::hasColumn('carts', 'variant_id')) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasColumn('carts', 'variant_id')) {
                $table->dropColumn('variant_id');
            }
        });
    }
};
