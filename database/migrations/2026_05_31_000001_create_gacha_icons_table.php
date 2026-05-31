<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catalog of reusable "coin form" gacha icons. Prizes reference a coin by
     * its `key`; the resolver falls back to a coin by reward type / subcategory
     * when a prize has no explicit icon.
     */
    public function up(): void
    {
        Schema::create('gacha_icons', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('category')->default('special');
            $table->string('image_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gacha_icons');
    }
};
