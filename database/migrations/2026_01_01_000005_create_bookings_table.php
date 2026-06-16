<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réservations — cœur métier. Repris de `bookings` ResaDZ, nettoyé :
 *  - supprimé : loueur_id, commission_*, client_service_fee, loueur_reviewed
 *  - client_id (users) → customer_id (customers)
 *  - ajouté : created_by (agent qui a créé la résa)
 * La table `reservations` (doublon ResaDZ) est abandonnée : tout passe ici.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // MED-2026-ABC123

            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Dates
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('total_days');

            // Lieux (livraison si module activé)
            $table->string('pickup_location')->nullable();
            $table->string('return_location')->nullable();
            $table->string('flight_number')->nullable();
            $table->text('pickup_notes')->nullable();
            $table->text('return_notes')->nullable();

            // Prix
            $table->decimal('base_price', 10, 2);
            $table->decimal('duration_discount', 10, 2)->default(0);
            $table->decimal('season_surcharge', 10, 2)->default(0);
            $table->decimal('options_total', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('extra_fees', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 3)->default('DZD');
            $table->json('selected_options')->nullable();

            // Caution
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->enum('deposit_status', ['pending', 'held', 'returned', 'partial', 'kept'])->default('pending');
            $table->text('deposit_notes')->nullable();

            // Acompte (avec timer d'expiration)
            $table->decimal('advance_amount', 10, 2)->nullable();
            $table->enum('advance_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->string('advance_payment_method')->nullable();
            $table->timestamp('advance_paid_at')->nullable();
            $table->timestamp('advance_expires_at')->nullable();

            // Paiement final
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('amount_remaining', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded'])->default('pending');

            // État des lieux (départ / retour)
            $table->json('photos_before')->nullable();
            $table->json('photos_after')->nullable();
            $table->text('condition_notes_before')->nullable();
            $table->text('condition_notes_after')->nullable();
            $table->integer('mileage_start')->nullable();
            $table->integer('mileage_end')->nullable();
            $table->string('fuel_level_start')->nullable();
            $table->string('fuel_level_end')->nullable();

            // Contrat
            $table->timestamp('contract_signed_at')->nullable();
            $table->string('contract_signature')->nullable();
            $table->string('contract_pdf')->nullable();

            // Cycle de vie (repris de ResaDZ)
            $table->enum('status', [
                'pending',    // en attente d'acompte
                'expired',    // délai acompte dépassé
                'confirmed',  // acompte reçu
                'active',     // véhicule pris
                'returning',  // retour prévu aujourd'hui
                'completed',  // véhicule rendu
                'cancelled',
                'dispute',
            ])->default('pending');

            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by')->nullable(); // customer | staff
            $table->string('source')->default('admin'); // admin | website | phone | whatsapp

            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'start_date']);
            $table->index(['vehicle_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
