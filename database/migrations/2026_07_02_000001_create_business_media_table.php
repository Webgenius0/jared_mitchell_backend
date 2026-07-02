<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');           // Stored path on disk (public disk)
            $table->string('file_name')->nullable(); // Original filename
            $table->string('mime_type')->nullable(); // e.g. image/jpeg, video/mp4
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_media');
    }
};
