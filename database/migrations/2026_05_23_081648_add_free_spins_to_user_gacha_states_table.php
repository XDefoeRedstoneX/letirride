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
        if (! Schema::hasTable('user_gacha_states')) {
            return;
        }

        Schema::table('user_gacha_states', function (Blueprint $table) {
            if (! Schema::hasColumn('user_gacha_states', 'free_spins')) {
                $table->unsignedInteger('free_spins')->default(0)->after('total_spins');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('user_gacha_states')) {
            return;
        }

        Schema::table('user_gacha_states', function (Blueprint $table) {
            if (Schema::hasColumn('user_gacha_states', 'free_spins')) {
                $table->dropColumn('free_spins');
            }
        });
    }
};
