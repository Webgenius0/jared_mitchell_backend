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
        Schema::create('event_ticket_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();

            $table->string('name'); // e.g. "Early Bird"
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->unsigned()->default(0.00);
            $table->decimal('service_fee', 8, 2)->unsigned()->default(0.00);
            $table->unsignedInteger('quantity_available')->nullable(); // null = unlimited
            $table->unsignedInteger('quantity_sold')->default(0);
            $table->dateTime('sale_starts_at')->nullable();
            $table->dateTime('sale_ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['event_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_ticket_tiers');
    }
};
