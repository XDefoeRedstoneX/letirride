<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prizes pick a coin from the icon catalog via `icon_key`. The existing
     * `image_path` stays as a full-custom override for hero prizes.
     */
    public function up(): void
    {
        Schema::table('gacha_pools', function (Blueprint $table) {
            $table->string('icon_key')->nullable()->index()->after('reward_type');
        });
    }

    public function down(): void
    {
        Schema::table('gacha_pools', function (Blueprint $table) {
            $table->dropIndex(['icon_key']);
            $table->dropColumn('icon_key');
        });
    }
};
