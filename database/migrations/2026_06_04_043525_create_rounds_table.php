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
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('round_session_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('round_number');

            $table->string('title');

            $table->text('goal')->nullable();

            $table->longText('requirements')->nullable();

            $table->boolean('is_active')->default(false);

            $table->integer('advance_limit')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->unique(['round_session_id', 'round_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
