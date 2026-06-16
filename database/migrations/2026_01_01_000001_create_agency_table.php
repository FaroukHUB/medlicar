<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'agence unique. Single-tenant : cette table ne contient qu'UNE ligne.
 * Remplace la table `loueurs` de ResaDZ (multi-loueurs supprimé).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency', function (Blueprint $table) {
            $table->id();

            // Identité
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();

            // Contact
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('wilaya')->nullable();

            // Réseaux sociaux (mini-site)
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();

            // Paramètres métier
            $table->string('currency', 3)->default('DZD');
            $table->decimal('vat_rate', 5, 2)->nullable();
            $table->string('timezone')->default('Africa/Algiers');
            $table->text('contract_terms')->nullable();   // CGV par défaut du contrat
            $table->json('payment_methods')->nullable();   // ["cash","cib","dahabia",...]

            // Acompte (paramétrable)
            $table->decimal('default_advance_percent', 5, 2)->default(30);
            $table->integer('advance_expiry_hours')->default(48); // timer d'expiration acompte

            // Modules activables par l'agence (off par défaut → pure location)
            $table->boolean('delivery_enabled')->default(false);
            $table->boolean('transfer_enabled')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency');
    }
};
