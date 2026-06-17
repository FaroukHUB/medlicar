<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Promotions véhicule : -X% sur une période (prix barré + badge auto). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedInteger('promo_discount_percent')->nullable()->after('promo_label');
            $table->date('promo_start')->nullable()->after('promo_discount_percent');
            $table->date('promo_end')->nullable()->after('promo_start');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['promo_discount_percent', 'promo_start', 'promo_end']);
        });
    }
};
