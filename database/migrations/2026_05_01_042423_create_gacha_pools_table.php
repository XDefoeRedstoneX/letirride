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
            $table->decimal('base_win_chance', 5, 2);
            $table->boolean('is_grand_prize')->default(false);
            $table->timestamps();
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
