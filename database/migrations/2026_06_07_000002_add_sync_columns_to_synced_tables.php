<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the logical-sync columns to every synced table (see config/sync.php and
 * docs/database-sync-plan.md §5):
 *
 *   - ulid       CHAR(26), nullable + UNIQUE  -> global identity across nodes.
 *                Nullable on purpose: existing rows are backfilled in Phase 2,
 *                and MySQL UNIQUE allows multiple NULLs meanwhile.
 *   - updated_at TIMESTAMP, nullable          -> added only where missing (LWW clock).
 *   - deleted_at TIMESTAMP, nullable          -> soft-delete tombstone (added where missing).
 *
 * Every column is guarded by hasColumn(), so this migration is idempotent and
 * safe to run on both the empty local DB and the populated CPanel DB.
 */
return new class extends Migration
{
    /** All synced tables (buckets 4.1-4.3 in the plan). */
    private array $tables = [
        // bidirectional
        'users', 'favorites', 'cart_items', 'tickets', 'gacha_histories', 'referrals',
        // cpanel_authority
        'orders', 'order_details', 'product_keys', 'gacha_payments', 'point_shop_purchases',
        'user_discounts', 'user_gacha_states', 'user_active_boosters', 'referral_rewards', 'topup_credentials',
        // admin_authority
        'categories', 'subcategories', 'products', 'product_discounts', 'discount_types',
        'faqs', 'news', 'gacha_pools', 'gacha_boosters', 'gacha_icons', 'gacha_rarity_chances',
        'point_shop_items', 'referral_configs', 'referral_tiers',
    ];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            if (! Schema::hasTable($name)) {
                continue;
            }

            Schema::table($name, function (Blueprint $table) use ($name) {
                if (! Schema::hasColumn($name, 'ulid')) {
                    $table->char('ulid', 26)->nullable()->unique();
                }
                if (! Schema::hasColumn($name, 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
                if (! Schema::hasColumn($name, 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            if (! Schema::hasTable($name)) {
                continue;
            }

            Schema::table($name, function (Blueprint $table) use ($name) {
                if (Schema::hasColumn($name, 'ulid')) {
                    $table->dropUnique([$name.'_ulid_unique']);
                    $table->dropColumn('ulid');
                }
                // Note: updated_at / deleted_at are intentionally NOT dropped on rollback,
                // since some tables had them originally. Drop manually if ever needed.
            });
        }
    }
};
