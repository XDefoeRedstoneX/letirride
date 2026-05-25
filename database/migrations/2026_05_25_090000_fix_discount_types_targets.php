<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repoint discount_types.target_category_id at the categories table (it was
     * originally constrained against products) and introduce a finer-grained
     * target_subcategory_id so brand-level vouchers (Steam, Netflix, etc.) can
     * scope to the correct subcategory rather than a sibling category.
     */
    public function up(): void
    {
        if (! Schema::hasTable('discount_types')) {
            return;
        }

        // Drop the mis-targeted foreign key. SQLite can't drop FKs cleanly, so we
        // null the column first to keep data consistent in either dialect.
        Schema::table('discount_types', function (Blueprint $table) {
            try {
                $table->dropForeign(['target_category_id']);
            } catch (\Throwable $e) {
                // FK may not exist (e.g. SQLite) — fall through.
            }
        });

        // Wipe any rows that pointed at product IDs the old FK was happy with;
        // the seeder will repopulate with correct category/subcategory targets.
        DB::table('discount_types')->update(['target_category_id' => null]);

        Schema::table('discount_types', function (Blueprint $table) {
            $table->foreign('target_category_id')
                ->references('id')->on('categories')
                ->nullOnDelete();

            if (! Schema::hasColumn('discount_types', 'target_subcategory_id')) {
                $table->foreignId('target_subcategory_id')
                    ->nullable()
                    ->after('target_category_id')
                    ->constrained('subcategories')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('discount_types')) {
            return;
        }

        Schema::table('discount_types', function (Blueprint $table) {
            if (Schema::hasColumn('discount_types', 'target_subcategory_id')) {
                $table->dropConstrainedForeignId('target_subcategory_id');
            }

            try {
                $table->dropForeign(['target_category_id']);
            } catch (\Throwable $e) {
                // ignore
            }

            // Restore the original (incorrect) FK so down() is symmetric with up().
            $table->foreign('target_category_id')
                ->references('id')->on('products')
                ->nullOnDelete();
        });
    }
};
