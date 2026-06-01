<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('referral_rewards')) {
            return;
        }

        // SQLite + MySQL friendly: drop & re-add `kind` as a wider string so we
        // don't have to wrestle enum modifications across drivers.
        if (Schema::hasColumn('referral_rewards', 'kind')) {
            Schema::table('referral_rewards', function (Blueprint $table) {
                $table->string('kind', 32)->change();
            });
        }

        // Allow milestone rows to live without a single owning referral.
        if (Schema::hasColumn('referral_rewards', 'referral_id')) {
            // Drop the FK so we can null the column; we re-add it after.
            try {
                Schema::table('referral_rewards', function (Blueprint $table) {
                    $table->dropForeign(['referral_id']);
                });
            } catch (\Throwable) {
                // FK name varies by driver — proceed regardless.
            }

            Schema::table('referral_rewards', function (Blueprint $table) {
                $table->foreignId('referral_id')->nullable()->change();
                $table->foreign('referral_id')->references('id')->on('referrals')->cascadeOnDelete();
            });
        }

        Schema::table('referral_rewards', function (Blueprint $table) {
            if (! Schema::hasColumn('referral_rewards', 'tier_id')) {
                $table->foreignId('tier_id')->nullable()->after('referral_id')->constrained('referral_tiers')->nullOnDelete();
            }
        });

        // One milestone tier can be paid to a recipient at most once. Existing
        // (referral_id, kind, order_id) unique stays for the other kinds.
        try {
            Schema::table('referral_rewards', function (Blueprint $table) {
                $table->unique(['recipient_id', 'kind', 'tier_id'], 'referral_rewards_milestone_unique');
            });
        } catch (\Throwable) {
            // Already exists from a prior partial run.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('referral_rewards')) {
            return;
        }

        try {
            Schema::table('referral_rewards', function (Blueprint $table) {
                $table->dropUnique('referral_rewards_milestone_unique');
            });
        } catch (\Throwable) {
        }

        if (Schema::hasColumn('referral_rewards', 'tier_id')) {
            Schema::table('referral_rewards', function (Blueprint $table) {
                try {
                    $table->dropForeign(['tier_id']);
                } catch (\Throwable) {
                }
                $table->dropColumn('tier_id');
            });
        }

        // Re-establish NOT NULL on referral_id and clear any milestone rows that
        // would violate it (they have referral_id = NULL).
        DB::table('referral_rewards')->whereNull('referral_id')->delete();

        if (Schema::hasColumn('referral_rewards', 'referral_id')) {
            try {
                Schema::table('referral_rewards', function (Blueprint $table) {
                    $table->dropForeign(['referral_id']);
                });
            } catch (\Throwable) {
            }

            Schema::table('referral_rewards', function (Blueprint $table) {
                $table->foreignId('referral_id')->nullable(false)->change();
                $table->foreign('referral_id')->references('id')->on('referrals')->cascadeOnDelete();
            });
        }
    }
};
