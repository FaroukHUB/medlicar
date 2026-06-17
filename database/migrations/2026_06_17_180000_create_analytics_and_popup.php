<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Mini-analytics (visites + clics WhatsApp) + popup promotionnel configurable. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_events', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('visit'); // visit | whatsapp
            $table->string('label')->nullable();       // chemin ou véhicule
            $table->timestamps();
            $table->index(['type', 'created_at']);
        });

        Schema::table('agency', function (Blueprint $table) {
            $table->boolean('popup_enabled')->default(false);
            $table->string('popup_title')->nullable();
            $table->text('popup_text')->nullable();
            $table->string('popup_image')->nullable();
            $table->string('popup_button_text')->nullable();
            $table->string('popup_button_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_events');
        Schema::table('agency', function (Blueprint $table) {
            $table->dropColumn(['popup_enabled', 'popup_title', 'popup_text', 'popup_image', 'popup_button_text', 'popup_button_url']);
        });
    }
};
