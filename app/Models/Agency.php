<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * L'agence unique (single-tenant). Une seule ligne en base.
 */
class Agency extends Model
{
    protected $table = 'agency';

    protected $guarded = [];

    protected $casts = [
        'payment_methods' => 'array',
        'vat_rate' => 'decimal:2',
        'default_advance_percent' => 'decimal:2',
        'delivery_enabled' => 'boolean',
        'transfer_enabled' => 'boolean',
        'show_eur' => 'boolean',
        'eur_rate' => 'decimal:2',
        'section_why' => 'boolean',
        'section_vehicles' => 'boolean',
        'section_reviews' => 'boolean',
        'section_stats' => 'boolean',
        'section_faq' => 'boolean',
        'section_contact' => 'boolean',
    ];

    /** Couleur primaire (boutons, en-têtes) avec repli. */
    public function colorPrimary(): string
    {
        return $this->color_primary ?: '#006233';
    }

    /** Couleur secondaire (CTA) avec repli. */
    public function colorSecondary(): string
    {
        return $this->color_secondary ?: '#D21034';
    }

    /** Récupère (et met en cache) l'agence unique. */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], ['name' => 'Medlicar']);
    }

    /** L'affichage en euros est-il actif et configuré ? */
    public function showsEur(): bool
    {
        return (bool) $this->show_eur && (float) $this->eur_rate > 0;
    }

    /** Convertit un montant en DA vers l'euro (arrondi entier), ou null si non configuré. */
    public function toEur($amountDa): ?int
    {
        if (! $this->showsEur()) {
            return null;
        }

        return (int) round((float) $amountDa / (float) $this->eur_rate);
    }
}
