<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topup_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_detail_id')->constrained()->cascadeOnDelete();
            $table->string('player_id');
            $table->string('zone_id')->nullable();
            $table->string('server_id')->nullable();
            $table->string('topup_status', 20)->default('pending');
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topup_credentials');
    }
};
