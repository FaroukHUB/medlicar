<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tunnel de réservation pro (calqué sur ResaDZ) :
 * plans de protection, options de retour, horaires d'ouverture, token client (upload documents).
 * Tout configurable depuis l'admin (template multi-agences).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->boolean('protection_enabled')->default(false);
            $table->unsignedInteger('protection_percent')->default(50); // % du prix de base pour la protection complète
            $table->json('protection_basic_details')->nullable();
            $table->json('protection_complete_details')->nullable();
            $table->unsignedTinyInteger('operating_hours_start')->default(8);
            $table->unsignedTinyInteger('operating_hours_end')->default(20);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('fuel_return_fee', 10, 2)->default(0)->after('deposit_amount');
            $table->decimal('wash_return_fee', 10, 2)->default(0)->after('fuel_return_fee');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('protection_plan')->nullable()->after('selected_options'); // none|basic|complete
            $table->decimal('protection_fee', 10, 2)->default(0)->after('protection_plan');
            $table->string('client_token', 64)->nullable()->unique()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->dropColumn(['protection_enabled', 'protection_percent', 'protection_basic_details', 'protection_complete_details', 'operating_hours_start', 'operating_hours_end']);
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['fuel_return_fee', 'wash_return_fee']);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['protection_plan', 'protection_fee', 'client_token']);
        });
    }
};
