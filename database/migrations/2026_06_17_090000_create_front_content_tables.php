<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bannières du slider d'accueil (éditables depuis l'admin).
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Catalogue global d'avantages (Livraison gratuite, Assistance 24/7…).
        Schema::create('advantages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable(); // nom d'icône heroicon (optionnel)
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Avantages affichés sur chaque véhicule (choisis dans le catalogue).
        Schema::create('advantage_vehicle', function (Blueprint $table) {
            $table->foreignId('advantage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->primary(['advantage_id', 'vehicle_id']);
        });

        // Vedette ("Notre sélection") + promo (badge simple) sur le véhicule.
        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->boolean('is_on_promo')->default(false)->after('is_featured');
            $table->string('promo_label')->nullable()->after('is_on_promo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advantage_vehicle');
        Schema::dropIfExists('advantages');
        Schema::dropIfExists('hero_slides');
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'is_on_promo', 'promo_label']);
        });
    }
};
