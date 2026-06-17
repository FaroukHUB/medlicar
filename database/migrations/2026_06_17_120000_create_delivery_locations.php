<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lieux de livraison/retour administrables + conditions enrichies.
 * Tout reste configurable (template multi-agences).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable(); // adresse ou précision
            $table->boolean('is_free')->default(false);
            $table->decimal('price', 10, 2)->default(0); // frais si payant
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('pickup_location_id')->nullable()->after('return_location')->constrained('delivery_locations')->nullOnDelete();
            $table->foreignId('return_location_id')->nullable()->after('pickup_location_id')->constrained('delivery_locations')->nullOnDelete();
        });

        // Conditions de location : PDF + case à cocher obligatoire (configurable).
        Schema::table('agency', function (Blueprint $table) {
            $table->string('terms_pdf')->nullable()->after('contract_terms');
            $table->boolean('require_terms')->default(false)->after('terms_pdf');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pickup_location_id');
            $table->dropConstrainedForeignId('return_location_id');
        });
        Schema::table('agency', function (Blueprint $table) {
            $table->dropColumn(['terms_pdf', 'require_terms']);
        });
        Schema::dropIfExists('delivery_locations');
    }
};
