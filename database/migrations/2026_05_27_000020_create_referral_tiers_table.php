<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('threshold')->unique();
            $table->string('title');
            $table->string('description')->nullable();
            $table->unsignedInteger('points_reward')->default(0);
            $table->foreignId('discount_type_id')->nullable()->constrained('discount_types')->nullOnDelete();
            $table->unsignedInteger('free_spins_reward')->default(0);
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_tiers');
    }
};
