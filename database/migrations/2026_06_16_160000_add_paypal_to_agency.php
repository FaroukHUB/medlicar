<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paiement en ligne de l'acompte via PayPal (seul moyen de paiement en ligne retenu).
 * PayPal ne supportant pas le DZD, l'acompte est converti dans une devise supportée
 * (EUR/USD) via un taux de conversion configurable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->boolean('paypal_enabled')->default(false);
            $table->string('paypal_mode')->default('sandbox'); // sandbox | live
            $table->text('paypal_client_id')->nullable();
            $table->text('paypal_secret')->nullable();
            $table->string('paypal_currency', 3)->default('EUR');
            $table->decimal('paypal_rate', 10, 2)->nullable(); // DA pour 1 unité de devise (ex: 150 = 1 EUR)
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('paypal_order_id')->nullable()->after('advance_payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->dropColumn(['paypal_enabled', 'paypal_mode', 'paypal_client_id', 'paypal_secret', 'paypal_currency', 'paypal_rate']);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('paypal_order_id');
        });
    }
};
