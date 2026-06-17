<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

/**
 * Prépare une nouvelle instance agence (template SaaS) en une commande.
 *   php artisan agency:provision --name="Top Cars" --email=admin@topcars.dz --password=secret --demo
 */
class AgencyProvision extends Command
{
    protected $signature = 'agency:provision
        {--name= : Nom de l\'agence}
        {--email= : Email de l\'administrateur}
        {--password= : Mot de passe de l\'administrateur}
        {--primary= : Couleur primaire (hex, ex #006233)}
        {--secondary= : Couleur secondaire (hex, ex #D21034)}
        {--demo : Charger le contenu de démonstration}';

    protected $description = "Configure une nouvelle instance agence (admin, branding, données de démo).";

    public function handle(): int
    {
        $this->info('— Provisioning d\'une instance agence —');

        $name = $this->option('name') ?: $this->ask('Nom de l\'agence', 'Mon Agence');
        $email = $this->option('email') ?: $this->ask('Email administrateur');
        $password = $this->option('password') ?: $this->secret('Mot de passe administrateur (laisser vide pour générer)');

        if (! $email) {
            $this->error('Un email administrateur est requis.');

            return self::FAILURE;
        }

        if (! $password) {
            $password = str()->password(12);
            $this->warn("Mot de passe généré : {$password}");
        }

        // 1) Compte administrateur (créé ou mis à jour)
        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Administrateur', 'password' => Hash::make($password)]
        );
        $this->line("✔ Administrateur : {$user->email}");

        // 2) Identité + branding de l'agence
        Agency::current()->update([
            'name' => $name,
            'color_primary' => $this->option('primary') ?: '#006233',
            'color_secondary' => $this->option('secondary') ?: '#D21034',
        ]);
        $this->line("✔ Agence : {$name}");

        // 3) Lien de stockage (logos, photos)
        Artisan::call('storage:link');
        $this->line('✔ Lien de stockage public');

        // 4) Contenu de démonstration (optionnel)
        if ($this->option('demo')) {
            Artisan::call('db:seed', ['--class' => 'DemoContentSeeder', '--force' => true]);
            $this->line('✔ Contenu de démonstration chargé');
        }

        $this->newLine();
        $this->info('Instance prête ✅');
        $this->table(['Étape suivante', 'Action'], [
            ['Domaine', 'Définir APP_URL dans .env et pointer le domaine du client'],
            ['Branding', 'Admin → Paramètres : logo, favicon, couleurs, coordonnées'],
            ['Flotte', 'Admin → Mes Véhicules : ajouter les voitures du client'],
            ['Cache', 'php artisan optimize'],
        ]);

        return self::SUCCESS;
    }
}
