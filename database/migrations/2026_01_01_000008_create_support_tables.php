<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Avis clients, notifications internes et journal d'audit (nouveauté Medlicar).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('comment')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('notifications_feed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete(); // null = toute l'agence
            $table->string('type');   // new_booking | return_due | overdue | payment_received | doc_expiring
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');       // booking.created, vehicle.updated…
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications_feed');
        Schema::dropIfExists('reviews');
    }
};
