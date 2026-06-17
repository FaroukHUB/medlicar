<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transforme le site en TEMPLATE dupliquable : tout le branding, le SEO et les
 * sections deviennent administrables (zéro code par nouvelle agence).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agency', function (Blueprint $table) {
            // Branding
            $table->string('slogan')->nullable()->after('legal_name');
            $table->string('favicon')->nullable()->after('logo');
            $table->string('og_image')->nullable()->after('cover_image');
            $table->string('color_primary')->default('#006233')->after('og_image');
            $table->string('color_secondary')->default('#D21034')->after('color_primary');

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Sections du site (activables/désactivables) + titres
            $table->boolean('section_why')->default(true);
            $table->boolean('section_vehicles')->default(true);
            $table->boolean('section_reviews')->default(true);
            $table->boolean('section_stats')->default(true);
            $table->boolean('section_faq')->default(true);
            $table->boolean('section_contact')->default(true);
            $table->string('why_title')->nullable();
            $table->string('reviews_title')->nullable();
            $table->string('stats_title')->nullable();
            $table->string('faq_title')->nullable();
            $table->string('contact_title')->nullable();
        });

        // « Pourquoi nous choisir »
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->nullable();
            $table->string('title');
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // FAQ
        Schema::create('faq_items', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Statistiques (chiffres clés)
        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('value');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
        Schema::dropIfExists('faq_items');
        Schema::dropIfExists('stats');
        Schema::table('agency', function (Blueprint $table) {
            $table->dropColumn([
                'slogan', 'favicon', 'og_image', 'color_primary', 'color_secondary',
                'meta_title', 'meta_description', 'meta_keywords',
                'section_why', 'section_vehicles', 'section_reviews', 'section_stats', 'section_faq', 'section_contact',
                'why_title', 'reviews_title', 'stats_title', 'faq_title', 'contact_title',
            ]);
        });
    }
};
