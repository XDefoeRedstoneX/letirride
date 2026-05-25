<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_keys')) {
            return;
        }

        Schema::table('product_keys', function (Blueprint $table) {
            if (! Schema::hasColumn('product_keys', 'reserved_for_order_id')) {
                $table->foreignId('reserved_for_order_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained('orders')
                    ->nullOnDelete();
            }

            $table->index(['product_id', 'status'], 'product_keys_product_status_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_keys')) {
            return;
        }

        Schema::table('product_keys', function (Blueprint $table) {
            try {
                $table->dropIndex('product_keys_product_status_idx');
            } catch (\Throwable $e) {
                // index may not exist
            }

            if (Schema::hasColumn('product_keys', 'reserved_for_order_id')) {
                $table->dropConstrainedForeignId('reserved_for_order_id');
            }
        });
    }
};
