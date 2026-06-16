<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clients de l'agence. Dans ResaDZ les clients étaient des `users` (role=client).
 * Ici on en fait une entité métier dédiée (CRM), distincte de l'équipe interne.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Coordonnées
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->date('birth_date')->nullable();

            // Pièces (avec dates d'expiration → alertes)
            $table->string('id_number')->nullable();        // CNI / passeport
            $table->string('id_document')->nullable();       // photo
            $table->string('license_number')->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('license_front')->nullable();
            $table->string('license_back')->nullable();
            $table->boolean('documents_verified')->default(false);

            // CRM
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_bookings')->default(0);
            $table->boolean('is_blacklisted')->default(false);
            $table->text('blacklist_reason')->nullable();
            $table->text('notes')->nullable(); // notes internes

            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
            $table->index('last_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
