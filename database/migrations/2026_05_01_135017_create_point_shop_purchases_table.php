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
            Schema::create('point_shop_purchases', function (Blueprint $table) {
                $table->id();
                
                // Links to the user who bought it
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                
                // Links to the specific item they bought from the shop
                $table->foreignId('point_shop_item_id')->constrained('point_shop_items')->cascadeOnDelete();
                
                // Snapshot of the cost.
                $table->integer('points_spent');
                
                // When they bought it
                $table->timestamp('created_at')->useCurrent(); 
            });
        }

    public function down(): void
    {
        Schema::dropIfExists('point_shop_purchases');
    }
};
