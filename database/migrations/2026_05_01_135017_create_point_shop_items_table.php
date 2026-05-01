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
            Schema::create('point_shop_items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('point_cost');
                $table->string('reward_type'); // e.g., 'discount_code', 'direct_product'
                // Links to your discount_types table. If a discount is deleted, this becomes null.
                $table->foreignId('discount_type_id')->nullable()->constrained('discount_types')->nullOnDelete();
                $table->string('img')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('point_shop_items');
        }
};
