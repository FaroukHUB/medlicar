<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tarification dynamique : règles (saison / durée / anticipée) + tarifs saisonniers.
 * Repris tel quel de ResaDZ (déjà single-tenant côté logique).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['seasonal', 'duration', 'early_booking']);

            // seasonal
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // duration
            $table->integer('min_days')->nullable();
            $table->integer('max_days')->nullable();

            // Modification de prix
            $table->enum('modifier_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('modifier_value', 10, 2);

            // Ciblage optionnel
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();

            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('seasonal_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price_per_day', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasonal_rates');
        Schema::dropIfExists('pricing_rules');
    }
};
