# ResaDZ → Medlicar — Mapping technique

Analyse du code réel de ResaDZ (Laravel + MySQL + Filament 3, hébergé o2switch),
poussé dans `legacy/resadz/` (Controllers, Models, migrations, vues Blade, routes).

ResaDZ est une **marketplace multi-loueurs** : 3 panels Filament (`admin` plateforme,
`loueur` agence, `chauffeur` VTC), pivot multi-tenant = `loueur_id`.

> **Découverte clé :** le panel **`loueur`** de ResaDZ EST déjà, à ~90 %, Medlicar.
> Ses pages = les modules demandés : `calendar`, `finance-dashboard`, `invoices`,
> `acomptes`, `messages`, `reviews`, `settings`, `conditions-location`.
> **Medlicar = extraire ce panel + retirer la couche marketplace au-dessus.**

---

## 1. Inventaire des tables : garder / supprimer / transformer

| Table ResaDZ | Décision Medlicar | Raison |
|---|---|---|
| `users` | **GARDER** (rôles revus) | Rôles `super_admin/loueur/client` → `owner/manager/agent/accountant` + clients |
| `loueurs` | **SUPPRIMER → fusionner** | Devient une table `agency` à 1 ligne (paramètres) |
| `loueur_settings` | **TRANSFORMER** | Fusionner dans `agency`/`settings` |
| `loueur_wilaya` | **SUPPRIMER** | Couverture géo marketplace |
| `vehicles` | **GARDER** | Retirer `loueur_id`, `is_featured`, champs boost/SEO marketplace |
| `brands`, `categories` | **GARDER** | Référentiel parc |
| `vehicle_models`, `vehicle_templates` | **GARDER (option)** | Aide à la saisie du parc |
| `options` | **GARDER** | Extras facturables (GPS, siège bébé…) |
| `bookings` | **GARDER (cœur)** | Déjà parfaite : EDL photo, caution, acompte+timer, contrat, 8 statuts |
| `reservations` | **SUPPRIMER (dette)** | Doublon simplifié de `bookings` → consolider sur `bookings` |
| `availabilities` | **GARDER** | Blocages calendrier (maintenance/manuel) |
| `time_slots` | **GARDER (option)** | Créneaux horaires prise/retour |
| `pricing_rules` | **GARDER** | Saison / durée / réservation anticipée |
| `seasonal_rates` | **GARDER** | Tarifs saisonniers |
| `transactions` + `expense_categories` | **GARDER** | Compta entrées/sorties → retirer `is_commission` |
| `invoices` + `invoice_items` | **GARDER** | Facturation |
| `reviews` | **GARDER (simplifier)** | Avis clients (retirer note loueur↔loueur) |
| `settings` | **GARDER** | Config agence (logo, CGV, devise…) |
| `conversations`, `messages` | **GARDER** | Messagerie client ↔ agence |
| `booking_conversations`, `booking_messages` | **GARDER** | Fil par réservation |
| `client_support_conversations/messages` | **GARDER (option)** | Support client |
| `push_subscriptions` | **GARDER** | Notifications push PWA |
| `page_visits`, `click_events` | **GARDER (option)** | Analytics du mini-site |
| `leads`, `prospects` | **GARDER (option)** | CRM commercial entrant |
| `hero_slides`, `popups`, `blog_posts` | **GARDER (option)** | Contenu du mini-site vitrine |
| `newsletters*`, `newsletter_subscribers` | **GARDER (option)** | Emailing clients |
| **MARKETPLACE — à SUPPRIMER** | | |
| `vehicle_boosts`, `boost_packages` | ❌ SUPPRIMER | Mise en avant payante = marketplace |
| `referrals`, `referral_rewards` | ❌ SUPPRIMER | Parrainage entre loueurs |
| `commission_*` (champs) | ❌ SUPPRIMER | Commissions plateforme |
| Stripe **Connect** (comptes liés) | ❌ SUPPRIMER | Split de paiement multi-vendeurs |
| `transfer_*`, `delivery_*`, `chauffeur_*`, `course_*` | ⚠️ OPTIONNEL | Services VTC/transfert/livraison : à garder **seulement si** l'agence les propose |
| `social_channels`, `featured_partners` | ❌ SUPPRIMER | Promotion marketplace |
| `admin_emails`, `prospect_kanban` (panel admin) | ❌ SUPPRIMER | Outils plateforme |

---

## 2. Champs marketplace à retirer des tables gardées

- **`vehicles`** : `loueur_id`, `is_featured`, `meta_title/description` (SEO marketplace), champs boost.
- **`bookings`** : `loueur_id`, `client_service_fee`, `commission_tier`, `loueur_reviewed`.
- **`users`** : rôle `loueur` (→ rôles internes), `subscription_plan` (vit sur `loueurs`).
- **`transactions`** : `is_commission`, `commission_rate`.

Tout le reste de `bookings` est **précieux** et se garde tel quel :
EDL photo avant/après, `mileage_start/end`, `fuel_level_*`, documents client
(`client_id_document`, `client_license_front/back`, `client_selfie`),
caution (`deposit_status`: pending→held→returned→partial→kept),
acompte avec **timer** (`advance_expires_at`), contrat (`contract_signed_at`,
`contract_signature`, `contract_pdf`), 8 statuts métier.

---

## 3. Machine à états de réservation (reprise de ResaDZ, validée)

```
pending → confirmed → active → returning → completed
   │                                          
 expired (timer acompte dépassé)              
   │                                          
 cancelled        dispute (litige)            
```

`pending` = en attente d'acompte ; `expired` = délai `advance_expires_at` dépassé
(libère le véhicule) ; `confirmed` = acompte reçu ; `active` = véhicule pris ;
`returning` = retour prévu aujourd'hui ; `completed` = rendu + soldé.
**Excellente base** — on la conserve.

---

## 4. Modèle de données cible (Medlicar)

Single-tenant : plus de `loueur_id`. Une table `agency` (1 ligne) porte les paramètres.

```
agency            (1 ligne : raison sociale, logo, devise, TVA, CGV, fuseau)
users             (owner | manager | agent | accountant)   ← équipe interne
customers         (ex-"client" : coordonnées, permis, CNI, blacklist, rating)
brands, categories, vehicle_models
vehicles          (parc : sans loueur_id ni boost/SEO)
options           (extras facturables)
bookings          (CŒUR : dates, prix, caution, acompte+timer, EDL, contrat, statut)
availabilities    (blocages calendrier : maintenance / manuel)
pricing_rules     (saison / durée / anticipée)
seasonal_rates
transactions + expense_categories   (comptabilité entrées/sorties)
invoices + invoice_items
reviews           (avis clients, simplifié)
conversations/messages + booking_messages   (messagerie)
settings
push_subscriptions, notifications
audit_logs        (NOUVEAU : traçabilité)
```

---

## 5. Panel Filament `loueur` → modules Medlicar

| Page Filament ResaDZ (`legacy/resadz/filament/loueur/`) | Module Medlicar |
|---|---|
| `calendar.blade.php` | Calendrier (jour/sem/mois, anti double-réservation) |
| `finance-dashboard.blade.php` | Statistiques / CA / trésorerie |
| `invoices.blade.php` | Facturation |
| `acomptes.blade.php` | Acomptes / paiements |
| `messages.blade.php`, `booking-messages.blade.php` | Messagerie |
| `reviews.blade.php` | Avis clients |
| `settings.blade.php` | Paramètres agence |
| `conditions-location.blade.php` | CGV / conditions contrat |
| `daily-briefing.blade.php` | Briefing quotidien (dashboard) |
| ~~`boost-vehicle.blade.php`~~ | ❌ marketplace |
| ~~`stripe-connect.blade.php`~~ | ❌ marketplace |
| ~~`onboarding*.blade.php`~~ | ❌ onboarding loueur |

---

## 6. Recommandation de stack — révisée après lecture du code

Mon 1er document proposait Next.js/Postgres. **Au vu du code existant, je révise :**

### ✅ Recommandé — Réutiliser la stack ResaDZ
**Laravel 11 + Filament 3 + MySQL** (o2switch ok), en repartant du panel `loueur`.

- On **récupère ~90 %** du métier déjà écrit et éprouvé (modèles, calcul de prix,
  contrat PDF, EDL, acompte, calendrier).
- Travail = **supprimer** la marketplace, pas réécrire l'existant.
- Tu maîtrises déjà cette stack et l'hébergement.
- Délai au MVP **bien plus court** qu'une réécriture.

### Alternative — Réécriture Next.js/Postgres
À ne choisir que si tu veux changer de techno volontairement (équipe JS, besoins
temps réel poussés). Sinon, c'est jeter de la valeur déjà construite. Le schéma
Prisma de `ARCHITECTURE.md` reste valable comme cible de cette voie.

---

## 7. Plan de transformation (voie Laravel/Filament recommandée)

1. **Nettoyer le schéma** : retirer `loueurs` (→ `agency`), `loueur_id` partout,
   tables boost/referral/commission, consolider `reservations` dans `bookings`.
2. **Rôles internes** : remplacer `loueur/client` par `owner/manager/agent/accountant`.
3. **Un seul panel Filament** (l'agence), basé sur l'ancien panel `loueur`.
4. **Retirer** Stripe Connect ; garder un paiement simple (acompte/solde) + cash.
5. **Garder** : véhicules, calendrier, bookings, contrat PDF, acomptes, factures,
   finance-dashboard, messagerie, reviews, settings.
6. **Optionnel** : mini-site de réservation à la marque de l'agence (ex-front marketplace
   réduit à une seule agence).
```
