<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pre-prod: existing activations don't translate cleanly from time → rolls.
        if (Schema::hasTable('user_active_boosters')) {
            DB::table('user_active_boosters')->delete();
        }

        if (Schema::hasTable('gacha_boosters') && Schema::hasColumn('gacha_boosters', 'duration_minutes')) {
            Schema::table('gacha_boosters', function (Blueprint $table) {
                $table->unsignedInteger('rolls_granted')->default(5)->after('bonus_percent');
            });

            DB::table('gacha_boosters')->update(['rolls_granted' => 5]);

            Schema::table('gacha_boosters', function (Blueprint $table) {
                $table->dropColumn('duration_minutes');
            });
        }

        if (Schema::hasTable('user_active_boosters')) {
            // Add the replacement column + its (user_id, rolls_remaining) index
            // FIRST so the user_id foreign key has an index to lean on before we
            // drop the old (user_id, expires_at) composite. Otherwise MySQL throws
            // error 1553 ("Cannot drop index ... needed in a foreign key constraint").
            Schema::table('user_active_boosters', function (Blueprint $table) {
                if (! Schema::hasColumn('user_active_boosters', 'rolls_remaining')) {
                    $table->unsignedInteger('rolls_remaining')->default(0)->after('gacha_booster_id');
                    $table->index(['user_id', 'rolls_remaining']);
                }
            });

            // Drop the now-redundant composite index. This runs in its own
            // Schema::table call so the try/catch actually wraps the executed SQL —
            // Blueprint commands only run when the closure returns, so a try/catch
            // around $table->dropIndex() inside one closure would never catch it.
            try {
                Schema::table('user_active_boosters', function (Blueprint $table) {
                    $table->dropIndex('user_active_boosters_user_id_expires_at_index');
                });
            } catch (\Throwable) {
                // Already dropped, or the driver named the index differently.
            }

            // Now the legacy time-based columns can go.
            Schema::table('user_active_boosters', function (Blueprint $table) {
                if (Schema::hasColumn('user_active_boosters', 'expires_at')) {
                    $table->dropColumn('expires_at');
                }
                if (Schema::hasColumn('user_active_boosters', 'activated_at')) {
                    $table->dropColumn('activated_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_active_boosters')) {
            // Re-add the legacy columns + (user_id, expires_at) index FIRST so the
            // user_id foreign key has a fallback before we drop the
            // (user_id, rolls_remaining) index (same MySQL 1553 trap as up()).
            Schema::table('user_active_boosters', function (Blueprint $table) {
                if (! Schema::hasColumn('user_active_boosters', 'activated_at')) {
                    $table->dateTime('activated_at')->nullable();
                }
                if (! Schema::hasColumn('user_active_boosters', 'expires_at')) {
                    $table->dateTime('expires_at')->nullable();
                    $table->index(['user_id', 'expires_at']);
                }
            });

            // Own Schema::table call so the try/catch wraps the executed SQL.
            try {
                Schema::table('user_active_boosters', function (Blueprint $table) {
                    $table->dropIndex('user_active_boosters_user_id_rolls_remaining_index');
                });
            } catch (\Throwable) {
                // Already dropped, or the driver named the index differently.
            }

            Schema::table('user_active_boosters', function (Blueprint $table) {
                if (Schema::hasColumn('user_active_boosters', 'rolls_remaining')) {
                    $table->dropColumn('rolls_remaining');
                }
            });
        }

        if (Schema::hasTable('gacha_boosters')) {
            Schema::table('gacha_boosters', function (Blueprint $table) {
                if (! Schema::hasColumn('gacha_boosters', 'duration_minutes')) {
                    $table->unsignedInteger('duration_minutes')->default(30)->after('bonus_percent');
                }
                if (Schema::hasColumn('gacha_boosters', 'rolls_granted')) {
                    $table->dropColumn('rolls_granted');
                }
            });
        }
    }
};
