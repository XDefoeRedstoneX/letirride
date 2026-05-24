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
        if (! Schema::hasTable('gacha_histories')) {
            return;
        }

        Schema::table('gacha_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('gacha_histories', 'cost_type')) {
                $table->enum('cost_type', ['points', 'money'])
                    ->default('points')
                    ->after('points_spent');
            }

            if (! Schema::hasColumn('gacha_histories', 'pity_triggered')) {
                $table->enum('pity_triggered', ['mini', 'hard'])
                    ->nullable()
                    ->after('cost_type');
            }

            if (! Schema::hasColumn('gacha_histories', 'reward_type')) {
                $table->string('reward_type')->nullable()->after('pity_triggered');
            }

            if (! Schema::hasColumn('gacha_histories', 'image_path')) {
                $table->string('image_path')->nullable()->after('reward_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('gacha_histories')) {
            return;
        }

        Schema::table('gacha_histories', function (Blueprint $table) {
            if (Schema::hasColumn('gacha_histories', 'image_path')) {
                $table->dropColumn('image_path');
            }

            if (Schema::hasColumn('gacha_histories', 'reward_type')) {
                $table->dropColumn('reward_type');
            }

            if (Schema::hasColumn('gacha_histories', 'pity_triggered')) {
                $table->dropColumn('pity_triggered');
            }

            if (Schema::hasColumn('gacha_histories', 'cost_type')) {
                $table->dropColumn('cost_type');
            }
        });
    }
};
