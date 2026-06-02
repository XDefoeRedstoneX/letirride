<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Point-shop items can now reward either a discount voucher (existing
     * discount_type_id) or a cashback points credit (points_amount). Normalise
     * the legacy free-text reward_type values to the two supported kinds.
     */
    public function up(): void
    {
        Schema::table('point_shop_items', function (Blueprint $table) {
            if (! Schema::hasColumn('point_shop_items', 'points_amount')) {
                $table->unsignedInteger('points_amount')->nullable()->after('discount_type_id');
            }
        });

        // Legacy rows used 'discount_code'; standardise on 'discount'.
        DB::table('point_shop_items')->where('reward_type', 'discount_code')->update(['reward_type' => 'discount']);
        DB::table('point_shop_items')->whereNotNull('discount_type_id')->update(['reward_type' => 'discount']);
    }

    public function down(): void
    {
        Schema::table('point_shop_items', function (Blueprint $table) {
            $table->dropColumn('points_amount');
        });
    }
};
