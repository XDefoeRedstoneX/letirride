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
        if (! Schema::hasTable('gacha_pools')) {
            return;
        }

        Schema::table('gacha_pools', function (Blueprint $table) {
            if (! Schema::hasColumn('gacha_pools', 'reward_type')) {
                $table->enum('reward_type', ['discount', 'points', 'free_spin', 'nothing'])
                    ->default('discount')
                    ->after('rarity_item');
            }

            if (! Schema::hasColumn('gacha_pools', 'points_amount')) {
                $table->unsignedInteger('points_amount')->nullable()->after('reward_type');
            }

            if (! Schema::hasColumn('gacha_pools', 'image_path')) {
                $table->string('image_path')->nullable()->after('points_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('gacha_pools')) {
            return;
        }

        Schema::table('gacha_pools', function (Blueprint $table) {
            if (Schema::hasColumn('gacha_pools', 'image_path')) {
                $table->dropColumn('image_path');
            }

            if (Schema::hasColumn('gacha_pools', 'points_amount')) {
                $table->dropColumn('points_amount');
            }

            if (Schema::hasColumn('gacha_pools', 'reward_type')) {
                $table->dropColumn('reward_type');
            }
        });
    }
};
