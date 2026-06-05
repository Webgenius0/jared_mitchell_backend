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
        Schema::create('contest_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')->constrained()->cascadeOnDelete();

            $table->foreignId('round_session_id')->constrained()->cascadeOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected', 'withdrawn'])->default('pending');

            $table->timestamp('approved_at')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('admin_note')->nullable();

            $table->timestamps();

            // One business can join only one session
            $table->unique('business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contest_applications');
    }
};
