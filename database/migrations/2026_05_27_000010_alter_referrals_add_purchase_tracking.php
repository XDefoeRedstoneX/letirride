<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('referrals')) {
            return;
        }

        Schema::table('referrals', function (Blueprint $table) {
            if (! Schema::hasColumn('referrals', 'first_purchase_order_id')) {
                $table->foreignId('first_purchase_order_id')->nullable()->after('reward_discount_id')->constrained('orders')->nullOnDelete();
            }
            if (! Schema::hasColumn('referrals', 'first_purchase_rewarded_at')) {
                $table->timestamp('first_purchase_rewarded_at')->nullable()->after('first_purchase_order_id');
            }
            if (! Schema::hasColumn('referrals', 'total_commission_paid')) {
                $table->unsignedInteger('total_commission_paid')->default(0)->after('first_purchase_rewarded_at');
            }
        });

        // Normalize existing rows: legacy 'rewarded' seeder value → 'first_purchase_rewarded'
        // so the new state machine is consistent from day one.
        DB::table('referrals')->where('status', 'rewarded')->update([
            'status' => 'first_purchase_rewarded',
            'first_purchase_rewarded_at' => now(),
        ]);

        if (Schema::hasColumn('referrals', 'reward_discount_id')) {
            Schema::table('referrals', function (Blueprint $table) {
                try {
                    $table->dropForeign(['reward_discount_id']);
                } catch (\Throwable) {
                    // FK name varies by driver — proceed regardless.
                }
                $table->dropColumn('reward_discount_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('referrals')) {
            return;
        }

        Schema::table('referrals', function (Blueprint $table) {
            if (! Schema::hasColumn('referrals', 'reward_discount_id')) {
                $table->foreignId('reward_discount_id')->nullable()->constrained('user_discounts')->nullOnDelete();
            }
            if (Schema::hasColumn('referrals', 'total_commission_paid')) {
                $table->dropColumn('total_commission_paid');
            }
            if (Schema::hasColumn('referrals', 'first_purchase_rewarded_at')) {
                $table->dropColumn('first_purchase_rewarded_at');
            }
            if (Schema::hasColumn('referrals', 'first_purchase_order_id')) {
                try {
                    $table->dropForeign(['first_purchase_order_id']);
                } catch (\Throwable) {
                }
                $table->dropColumn('first_purchase_order_id');
            }
        });

        DB::table('referrals')->where('status', 'first_purchase_rewarded')->update(['status' => 'rewarded']);
    }
};
