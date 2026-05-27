<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('gacha_pools') && Schema::hasColumn('gacha_pools', 'base_win_chance')) {
            Schema::table('gacha_pools', function (Blueprint $table) {
                $table->dropColumn('base_win_chance');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gacha_pools') && ! Schema::hasColumn('gacha_pools', 'base_win_chance')) {
            Schema::table('gacha_pools', function (Blueprint $table) {
                $table->decimal('base_win_chance', 5, 2)->default(0);
            });
        }
    }
};
