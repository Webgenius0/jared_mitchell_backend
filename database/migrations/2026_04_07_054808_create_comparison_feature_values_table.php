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
        Schema::create('comparison_feature_values', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('pricing_plan_id')
                  ->constrained('pricing_plans')
                  ->cascadeOnDelete();
 
            $table->foreignId('comparison_feature_id')
                  ->constrained('comparison_features')
                  ->cascadeOnDelete();
 
            // The display value for this plan's cell.
            // Examples: "Yes", "No", "Unlimited", "Priority",
            // "2/week (3 posts/day)", "Full Dashboard + trend reports"
            $table->string('display_value', 255);
 
            // How to render the cell value in the UI
            $table->enum('value_type', [
                'boolean_yes',  // green checkmark  ✓
                'boolean_no',   // red cross / dash  ✗
                'text',         // plain string, e.g. "Priority", "Basic Insights"
                'number',       // numeric quantity, e.g. "1–2", "3–6"
            ])->default('text');
 
            $table->timestamps();
 
            // Each plan can only have one value per feature
            $table->unique(['pricing_plan_id', 'comparison_feature_id'], 'unique_plan_feature');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparison_feature_values');
    }
};
