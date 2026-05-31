<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || Schema::hasColumn('users', 'referrals_last_seen_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('referrals_last_seen_at')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'referrals_last_seen_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('referrals_last_seen_at');
            });
        }
    }
};
