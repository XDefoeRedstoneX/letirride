<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Add a dedicated index for the user_id FK before dropping the composite
            // unique index, which MySQL was using to satisfy that FK requirement.
            $table->index('user_id', 'cart_items_user_id_index');
            $table->dropUnique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id']);
            $table->dropIndex('cart_items_user_id_index');
        });
    }
};
