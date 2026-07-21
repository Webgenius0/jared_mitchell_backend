<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Spotlight Vote Purchases — paid vote packages purchased by spotlight owners.
     * Max 100 purchased votes per nominee per week (Option A — Most Fair).
     *
     * Pricing packages:
     *   starter  = 1 vote  / $1.00
     *   popular  = 10 votes / $8.00
     *   boost    = 25 votes / $18.00
     *   power    = 50 votes / $35.00
     *
     * Flow: Owner requests → Admin approves → votes_count credited to nominee.
     */
    public function up(): void
    {
        Schema::create('spotlight_vote_purchases', function (Blueprint $table) {
            $table->id();

            // Which nominee the purchased votes go to
            $table->foreignId('spotlight_week_nominee_id')
                  ->constrained('spotlight_week_nominees')
                  ->cascadeOnDelete();

            // Who purchased (spotlight owner / their account)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Vote package selected
            $table->enum('package', ['starter', 'popular', 'boost', 'power'])
                  ->comment('starter=1vote/$1 | popular=10votes/$8 | boost=25votes/$18 | power=50votes/$35');

            // How many votes this purchase adds
            $table->unsignedTinyInteger('votes_count')
                  ->comment('1, 10, 25, or 50');

            // Amount charged
            $table->decimal('amount_paid', 8, 2)
                  ->comment('1.00, 8.00, 18.00, or 35.00');

            // Optional link to Order (if integrated with Stripe/Cart flow later)
            $table->foreignId('order_id')
                  ->nullable()
                  ->constrained('orders')
                  ->nullOnDelete();

            // Purchase status
            // pending   = request submitted, awaiting admin approval
            // completed = admin approved, votes credited to nominee
            // refunded  = purchase refunded, votes removed
            $table->enum('status', ['pending', 'completed', 'refunded'])
                  ->default('pending')
                  ->index();

            // Admin who approved / rejected
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['spotlight_week_nominee_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotlight_vote_purchases');
    }
};
