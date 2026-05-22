<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gacha_histories', function (Blueprint $table) {
            $table->foreignId('gacha_payment_id')
                ->nullable()
                ->after('points_spent')
                ->constrained('gacha_payments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gacha_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gacha_payment_id');
        });
    }
};
