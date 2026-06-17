<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Affichage du prix en euros à côté du dinar (utile pour la diaspora).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->boolean('show_eur')->default(true)->after('currency');
            $table->decimal('eur_rate', 10, 2)->nullable()->after('show_eur'); // DA pour 1 €
        });
    }

    public function down(): void
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->dropColumn(['show_eur', 'eur_rate']);
        });
    }
};
