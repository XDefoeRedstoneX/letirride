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
            if (Schema::hasColumn('user_active_boosters', 'expires_at')) {
                // Ensure a standalone user_id index exists before dropping the composite one,
                // because MySQL won't drop an index that is the sole covering index for a FK.
                try {
                    DB::statement('CREATE INDEX user_active_boosters_user_id_index ON user_active_boosters (user_id)');
                } catch (\Throwable) {}
                try {
                    DB::statement('DROP INDEX user_active_boosters_user_id_expires_at_index ON user_active_boosters');
                } catch (\Throwable) {}

                Schema::table('user_active_boosters', function (Blueprint $table) {
                    $table->dropColumn('expires_at');
                });
            }

            Schema::table('user_active_boosters', function (Blueprint $table) {
                if (Schema::hasColumn('user_active_boosters', 'activated_at')) {
                    $table->dropColumn('activated_at');
                }
                if (! Schema::hasColumn('user_active_boosters', 'rolls_remaining')) {
                    $table->unsignedInteger('rolls_remaining')->default(0)->after('gacha_booster_id');
                    $table->index(['user_id', 'rolls_remaining']);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_active_boosters')) {
            Schema::table('user_active_boosters', function (Blueprint $table) {
                if (Schema::hasColumn('user_active_boosters', 'rolls_remaining')) {
                    try {
                        $table->dropIndex(['user_id', 'rolls_remaining']);
                    } catch (\Throwable) {
                    }
                    $table->dropColumn('rolls_remaining');
                }
                if (! Schema::hasColumn('user_active_boosters', 'activated_at')) {
                    $table->dateTime('activated_at')->nullable();
                }
                if (! Schema::hasColumn('user_active_boosters', 'expires_at')) {
                    $table->dateTime('expires_at')->nullable();
                    $table->index(['user_id', 'expires_at']);
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
