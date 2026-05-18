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

        if (! Schema::hasColumn('gacha_pools', 'points_reward')) {
            return;
        }

        Schema::table('gacha_pools', function (Blueprint $table) {
            $table->dropColumn('points_reward');
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

        if (Schema::hasColumn('gacha_pools', 'points_reward')) {
            return;
        }

        Schema::table('gacha_pools', function (Blueprint $table) {
            $table->integer('points_reward')->nullable()->after('discount_type_id');
        });
    }
};
