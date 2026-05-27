<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->constrained('referrals')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->enum('kind', ['referee_welcome', 'first_purchase', 'commission']);
            $table->unsignedInteger('points_amount');
            $table->timestamp('created_at')->useCurrent();

            // Idempotency guard: a referral can pay a given kind for a given order
            // (or null order, for welcome) exactly once. Stops duplicate webhook
            // callbacks from re-paying.
            $table->unique(['referral_id', 'kind', 'order_id'], 'referral_rewards_idem_unique');
            $table->index(['recipient_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
    }
};
