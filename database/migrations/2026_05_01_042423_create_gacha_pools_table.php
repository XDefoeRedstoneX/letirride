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
        Schema::create('gacha_pools', function (Blueprint $table) {
            $table->id();
            $table->string('prize_name');
            $table->foreignId('discount_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rarity_item');
            $table->decimal('base_win_chance', 5, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gacha_pools');
    }
};
