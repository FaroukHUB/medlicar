<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référentiel du parc : marques, catégories, et les véhicules eux-mêmes.
 * Repris de ResaDZ, débarrassé du multi-tenant (loueur_id) et du SEO marketplace.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');             // Économique, SUV, Luxe, Utilitaire…
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->string('model');                 // Tiguan 2025
            $table->string('full_name');             // VW Tiguan 2025
            $table->string('slug')->unique();
            $table->string('plate')->nullable()->unique();   // immatriculation
            $table->string('vin')->nullable()->unique();
            $table->string('year')->nullable();

            // Tarification
            $table->decimal('price_per_day', 10, 2);
            $table->decimal('price_per_week', 10, 2)->nullable();
            $table->decimal('price_per_month', 10, 2)->nullable();
            $table->decimal('deposit_amount', 10, 2)->default(0); // caution

            // Caractéristiques
            $table->string('transmission')->default('automatic'); // automatic | manual
            $table->string('fuel_type')->default('diesel');       // diesel | essence | hybrid | electric | gpl
            $table->integer('seats')->default(5);
            $table->integer('doors')->default(5);
            $table->integer('luggage_capacity')->nullable();
            $table->boolean('has_ac')->default(true);
            $table->integer('mileage')->default(0);
            $table->json('features')->nullable();

            // Images
            $table->string('image')->nullable();     // image principale
            $table->json('gallery')->nullable();

            // État
            $table->enum('status', ['available', 'rented', 'maintenance', 'unavailable'])
                  ->default('available');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();

            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        // Documents véhicule (assurance, contrôle technique…) avec expiration → alertes
        Schema::create('vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('type');   // insurance | technical_inspection | registration
            $table->string('file')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        // Options / extras facturables (GPS, siège bébé, conducteur additionnel…)
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('price_type', ['per_day', 'per_booking'])->default('per_booking');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('options');
        Schema::dropIfExists('vehicle_documents');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
    }
};
