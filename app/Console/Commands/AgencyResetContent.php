<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vide le contenu de démonstration/opérationnel pour livrer une instance propre.
 * Conserve : utilisateurs, paramètres de l'agence, catégories, marques (données de référence).
 *   php artisan agency:reset-content --force
 */
class AgencyResetContent extends Command
{
    protected $signature = 'agency:reset-content {--force : Exécuter sans confirmation}';

    protected $description = "Vide le contenu (véhicules, réservations, démos…) pour une livraison propre.";

    /** Tables vidées (de l'enfant vers le parent). Réference/config conservées. */
    private const TABLES = [
        'reviews', 'transactions', 'invoices', 'bookings',
        'availabilities', 'maintenances', 'vehicle_documents',
        'advantage_vehicle', 'vehicles', 'customers',
        'hero_slides', 'features', 'faq_items', 'stats', 'advantages',
        'expenses', 'notifications',
    ];

    public function handle(): int
    {
        $this->warn('Cette action SUPPRIME : véhicules, réservations, clients, avis, bannières, avantages, FAQ, stats…');
        $this->line('Conserve : utilisateurs, paramètres agence, catégories, marques, options, règles de prix.');

        if (! $this->option('force') && ! $this->confirm('Confirmer la remise à blanc ?')) {
            $this->info('Annulé.');

            return self::SUCCESS;
        }

        Schema::disableForeignKeyConstraints();
        $cleared = [];
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->delete();
                $cleared[] = [$table, $count];
            }
        }
        Schema::enableForeignKeyConstraints();

        $this->table(['Table vidée', 'Lignes supprimées'], $cleared);
        $this->info('Instance remise à blanc ✅ — prête pour le client.');

        return self::SUCCESS;
    }
}
