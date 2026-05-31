<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-user win cap for a prize. NULL = unlimited. When a user has already
     * won a prize `max_per_user` times, it's dropped from their eligible roll
     * pool (e.g. the one-time "Welcome Bonus").
     */
    public function up(): void
    {
        Schema::table('gacha_pools', function (Blueprint $table) {
            $table->unsignedInteger('max_per_user')->nullable()->after('icon_key');
        });
    }

    public function down(): void
    {
        Schema::table('gacha_pools', function (Blueprint $table) {
            $table->dropColumn('max_per_user');
        });
    }
};
