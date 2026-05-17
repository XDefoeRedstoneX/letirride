<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Adds the 'img' column after 'point_reward', allowing it to be null
            $table->string('img')->nullable()->after('point_reward');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drops the column if you ever need to rollback
            $table->dropColumn('img');
        });
    }
};
