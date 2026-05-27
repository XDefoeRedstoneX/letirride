<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('referee_welcome_points')->default(100);
            $table->unsignedInteger('referrer_first_purchase_pts')->default(500);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->unsignedInteger('commission_per_order_cap')->default(5000);
            $table->unsignedInteger('commission_lifetime_cap')->default(50000);
            $table->unsignedInteger('referrer_min_account_age_h')->default(0);
            $table->timestamps();
        });

        DB::table('referral_configs')->insert([
            'id' => 1,
            'referee_welcome_points' => 100,
            'referrer_first_purchase_pts' => 500,
            'commission_percent' => 0,
            'commission_per_order_cap' => 5000,
            'commission_lifetime_cap' => 50000,
            'referrer_min_account_age_h' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_configs');
    }
};
