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
        Schema::create('section_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('field_key');
            $table->string('field_type')->comment('e.g., text | image | richtext | url | boolean');
            $table->text('value')->nullable();
            $table->string('locale')->default('en');
            // section_id, field_key, field_type, value, locale
            $table->timestamps();

            $table->unique(['section_id', 'field_key', 'locale']);
            $table->index(['section_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_contents');
    }
};
