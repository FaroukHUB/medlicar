<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Bibliothèque de modèles de véhicules (specs pré-remplies) pour accélérer la saisie. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ex : Hyundai Tucson
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transmission')->nullable();
            $table->string('fuel_type')->nullable();
            $table->unsignedInteger('seats')->nullable();
            $table->unsignedInteger('doors')->nullable();
            $table->unsignedInteger('luggage_capacity')->nullable();
            $table->boolean('has_ac')->default(true);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_templates');
    }
};
