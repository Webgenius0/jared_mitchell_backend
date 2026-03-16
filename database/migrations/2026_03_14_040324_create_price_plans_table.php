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
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();

            $table->string('plan_name', 100);        
            $table->string('badge_text', 50)->nullable();    
            $table->decimal('price', 10, 2);         
            $table->string('price_suffix', 20);      
            $table->text('best_for');               
            $table->text('outcome_text');            
            $table->string('button_label', 50);    
            $table->string('button_url', 255);
            $table->boolean('is_featured')->default(0);
            $table->boolean('is_visible')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
