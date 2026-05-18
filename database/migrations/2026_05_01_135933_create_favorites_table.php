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
            Schema::create('favorites', function (Blueprint $table) {
                $table->id();
                
                // Link to the user
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                
                // Link to the product they favorited
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                
                // Prevent a user from favoriting the exact same product twice
                $table->unique(['user_id', 'product_id']);
                
                // We only need to know when they favorited it
                $table->timestamp('created_at')->useCurrent();
            });
        }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
