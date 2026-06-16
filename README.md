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

## Installation locale

```bash
composer install
cp .env.example .env
php artisan key:generate

# DB : SQLite (rapide) ou MySQL (prod o2switch)
touch database/database.sqlite      # si SQLite
php artisan migrate --seed

npm install && npm run build        # assets
php artisan serve
```

### Connexion back-office

- URL : `http://localhost:8000/app`
- Email : `owner@medlicar.test`
- Mot de passe : `password`

> ⚠️ Compte de démonstration — à changer avant toute mise en production.

## Configuration MySQL (production)

Dans `.env` :

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medlicar
DB_USERNAME=...
DB_PASSWORD=...
```
