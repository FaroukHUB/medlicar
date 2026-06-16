# Medlicar

ERP / CRM de gestion pour **une agence de location de voitures** (single-tenant).
Construit en **Laravel 11 + Filament 3 + MySQL**, à partir de l'expérience ResaDZ,
sans la couche marketplace.

> Documents de conception : [`BLUEPRINT.md`](BLUEPRINT.md) (référence),
> [`MAPPING-RESADZ.md`](MAPPING-RESADZ.md) (analyse du legacy),
> [`ARCHITECTURE.md`](ARCHITECTURE.md) (voie Next.js, non retenue).
> Le code d'origine ResaDZ est conservé en lecture seule dans [`legacy/resadz/`](legacy/resadz/).

## État d'avancement

**Phase 0 — Fondation (faite)**
- Projet Laravel 11 + Filament 3, panel unique **« Medlicar »** (`/app`).
- Schéma single-tenant : `agency` (1 ligne), `users` (rôles internes), `customers`,
  `vehicles`/`brands`/`categories`/`options`, `bookings`, `availabilities`,
  `pricing_rules`/`seasonal_rates`, `transactions`/`invoices`, `reviews`, `audit_logs`.
- Modèles Eloquent + relations + anti double-réservation (`Vehicle::isAvailableBetween`).
- Ressources Filament : Véhicules, Clients, Réservations, Marques, Catégories, Options.
- Modules **Livraison** / **Transfert** activables via `agency.delivery_enabled` / `transfer_enabled`.

**À suivre** — Phase 1 (MVP : calendrier, cycle réservation, contrat PDF, acomptes,
dashboard), Phase 2 (factures, stats, notifications), Phase 3 (mini-site, services).

## Base de données

**MySQL / MariaDB** (cible de production : hébergement mutualisé **o2switch**).
Le schéma a été validé sur **MariaDB 10.11** (`migrate:fresh --seed` OK).
`.env.example` est déjà configuré pour MySQL — il suffit de renseigner tes accès.

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medlicar
DB_USERNAME=medlicar
DB_PASSWORD=********
```

## Installation

```bash
composer install
cp .env.example .env       # puis renseigner les accès MySQL
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

### Connexion back-office

- URL : `http://localhost:8000/app`
- Email : `owner@medlicar.test`
- Mot de passe : `password`

> ⚠️ Compte de démonstration — à changer avant toute mise en production.

> Astuce dev : pour un essai local sans serveur MySQL, on peut basculer
> `DB_CONNECTION=sqlite` + `touch database/database.sqlite`. **La production reste MySQL.**
