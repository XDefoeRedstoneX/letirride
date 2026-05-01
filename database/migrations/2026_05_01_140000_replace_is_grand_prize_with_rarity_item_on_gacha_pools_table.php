<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        if (! Schema::hasColumn('gacha_pools', 'rarity_item')) {
            Schema::table('gacha_pools', function (Blueprint $table) {
                $table->string('rarity_item')->default('common')->after('discount_type_id');
            });
        }

        // Backfill rarity based on existing prize naming.
        DB::table('gacha_pools')->where('prize_name', 'like', 'Grand Prize:%')->update(['rarity_item' => 'grand_prize']);
        DB::table('gacha_pools')->where('prize_name', 'like', 'Legendary:%')->update(['rarity_item' => 'legendary']);
        DB::table('gacha_pools')->where('prize_name', 'like', 'Epic:%')->update(['rarity_item' => 'epic']);
        DB::table('gacha_pools')->where('prize_name', 'like', 'Rare:%')->update(['rarity_item' => 'rare']);
        DB::table('gacha_pools')->where('prize_name', 'like', 'Common:%')->update(['rarity_item' => 'common']);

        if (Schema::hasColumn('gacha_pools', 'is_grand_prize')) {
            Schema::table('gacha_pools', function (Blueprint $table) {
                $table->dropColumn('is_grand_prize');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('gacha_pools')) {
            return;
        }

        if (! Schema::hasColumn('gacha_pools', 'is_grand_prize')) {
            Schema::table('gacha_pools', function (Blueprint $table) {
                $table->boolean('is_grand_prize')->default(false)->after('base_win_chance');
            });
        }

        DB::table('gacha_pools')->where('rarity_item', 'grand_prize')->update(['is_grand_prize' => true]);

        if (Schema::hasColumn('gacha_pools', 'rarity_item')) {
            Schema::table('gacha_pools', function (Blueprint $table) {
                $table->dropColumn('rarity_item');
            });
        }
    }
};
