<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Make tickets support guest submissions + an email-based reply flow:
     *  - user_id becomes nullable (guests have none)
     *  - email is the customer's reply-to (required at the app layer)
     *  - subject categorises the request; ip_address aids abuse triage
     *  - updated_at lets status changes carry a timestamp
     * Also normalises the legacy "resolved" status to "closed".
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'email')) {
                $table->string('email')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('tickets', 'name')) {
                $table->string('name')->nullable()->after('email');
            }
            if (! Schema::hasColumn('tickets', 'subject')) {
                $table->string('subject')->nullable()->after('type');
            }
            if (! Schema::hasColumn('tickets', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('status');
            }
            if (! Schema::hasColumn('tickets', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });

        // user_id nullable so guests can file tickets.
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        // Backfill existing rows so they fit the new shape.
        DB::table('tickets')->where('status', 'resolved')->update(['status' => 'closed']);

        DB::table('tickets')->whereNull('email')->orWhere('email', '')->get()->each(function ($t) {
            $email = $t->user_id
                ? DB::table('users')->where('id', $t->user_id)->value('email')
                : null;
            DB::table('tickets')->where('id', $t->id)->update([
                'email' => $email ?: 'unknown@ridly.example',
                'subject' => $t->subject ?: Str::limit((string) $t->message, 57, '...'),
                'updated_at' => $t->created_at,
            ]);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropColumn(['email', 'name', 'subject', 'ip_address', 'updated_at']);
        });
    }
};
