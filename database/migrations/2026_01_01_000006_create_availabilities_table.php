<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blocages de disponibilité du calendrier (maintenance, indispo manuelle, réservé).
 * Sert à la prévention des doubles réservations + vue planning.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('type', ['blocked', 'maintenance', 'reserved'])->default('blocked');
            $table->text('reason')->nullable();
            $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['vehicle_id', 'start_date', 'end_date']);
        });

        // Suivi d'entretien du parc
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('type');                  // vidange, pneus, révision…
            $table->decimal('cost', 10, 2)->nullable();
            $table->date('date');
            $table->date('next_due_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('availabilities');
    }
};
