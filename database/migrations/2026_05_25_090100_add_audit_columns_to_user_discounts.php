<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_discounts')) {
            return;
        }

        Schema::table('user_discounts', function (Blueprint $table) {
            if (! Schema::hasColumn('user_discounts', 'used_at')) {
                $table->timestamp('used_at')->nullable()->after('is_used');
            }

            if (! Schema::hasColumn('user_discounts', 'order_id')) {
                $table->foreignId('order_id')
                    ->nullable()
                    ->after('used_at')
                    ->constrained('orders')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_discounts')) {
            return;
        }

        Schema::table('user_discounts', function (Blueprint $table) {
            if (Schema::hasColumn('user_discounts', 'order_id')) {
                $table->dropConstrainedForeignId('order_id');
            }

            if (Schema::hasColumn('user_discounts', 'used_at')) {
                $table->dropColumn('used_at');
            }
        });
    }
};
