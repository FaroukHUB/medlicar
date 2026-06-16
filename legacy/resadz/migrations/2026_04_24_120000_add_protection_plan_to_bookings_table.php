<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('protection_plan', 20)->default('basic')->after('selected_options');
            $table->decimal('protection_supplement', 10, 2)->default(0)->after('protection_plan');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['protection_plan', 'protection_supplement']);
        });
    }
};
