# Medlicar — Blueprint (décisions validées)

Document de référence. Reflète les choix actés avec le porteur du projet.
Voir aussi : `MAPPING-RESADZ.md` (analyse du legacy), `ARCHITECTURE.md` (voie Next.js,
**non retenue** — conservée comme alternative).

## Décisions

| Sujet | Choix |
|---|---|
| Stack | **Laravel 11 + Filament 3 + MySQL** (hébergement o2switch). On repart de ResaDZ. |
| Méthode | **Retirer** la marketplace du code existant, **pas** réécrire. |
| Mini-site public | **Oui** — site de réservation à la marque de l'agence (1 agence, pas une marketplace). |
| Services | Location **toujours** active. **Livraison** et **Transfert/VTC** = **modules activables** par l'agence. |
| Multi-tenant | **Supprimé.** Single-tenant : 1 agence, table `agency` à 1 ligne. |

---

## 1. Architecture cible

```
┌─────────────────────────────────────────────────────────────┐
│  Mini-site public (Blade)        Back-office (Filament 3)     │
│  réservation à la marque         panel unique "agence"        │
│  — modules selon services        owner/manager/agent/compta   │
└───────────────┬──────────────────────────────┬──────────────┘
                │            Laravel 11          │
│  Modules : Vehicles · Bookings · Customers · Calendar ·       │
│            Pricing · Payments/Acomptes · Invoices · Stats ·   │
│            Messaging · Reviews · Settings · [Delivery] ·      │
│            [Transfer]                                          │
│  Services : Auth/RBAC · Contract PDF · Notifications · Audit  │
└───────────────┬───────────────────────────────────────────────┘
                │ Eloquent
        ┌───────┴────────┐
        │  MySQL (o2switch)  ·  Storage (photos, PDF)  │
        └────────────────────────────────────────────────┘
```

- **Un seul panel Filament** : l'ancien panel `loueur` de ResaDZ, nettoyé. Les panels
  `admin` (plateforme) et `chauffeur` (séparé) disparaissent en tant que tels.
- **Rôles internes** (sur `users`) : `owner`, `manager`, `agent`, `accountant`.
  Les clients ne sont plus des `users` mais des `customers`.

## 2. Modules activables (feature flags)

Stockés dans `agency` (ou `settings`) :

| Flag | Effet si activé |
|---|---|
| `delivery_enabled` | Livraison/reprise du véhicule à une adresse (zones + tarifs). Affiche le module et l'option côté mini-site. |
| `transfer_enabled` | Transferts aéroport / VTC (routes + tarifs + chauffeurs). Idem. |

Par défaut les deux sont **désactivés** → l'app se comporte comme une pure location.
Filament et le mini-site **masquent** ces modules tant que le flag est off.

## 3. Schéma cible (à partir du schéma ResaDZ nettoyé)

**Conservé tel quel (ou quasi) :**
`vehicles` (sans `loueur_id`, `is_featured`, SEO marketplace) · `brands` · `categories`
· `vehicle_models` · `options` · `bookings` (sans `loueur_id`, `commission_*`,
`client_service_fee`, `loueur_reviewed`) · `availabilities` · `pricing_rules`
· `seasonal_rates` · `transactions` + `expense_categories` (sans `is_commission`)
· `invoices` + `invoice_items` · `reviews` (simplifié) · `conversations`/`messages`
· `booking_conversations`/`booking_messages` · `settings` · `push_subscriptions`.

**Nouveau / transformé :**
- `agency` (1 ligne) ← remplace `loueurs` : identité, logo, devise, TVA, CGV, fuseau,
  `delivery_enabled`, `transfer_enabled`.
- `users` : rôles → `owner/manager/agent/accountant`.
- `customers` ← ex-clients (table dédiée, plus des `users`) : coordonnées, permis, CNI,
  blacklist, rating, historique.
- `audit_logs` (nouveau) : traçabilité des actions.

**Modules services (seulement si flag actif) :**
- Livraison : `delivery_zones`, `delivery_rates`, `delivery_bookings`.
- Transfert : `transfer_routes`, `transfer_bookings`, `chauffeur_vehicles`,
  `chauffeur_options`, `course_availabilities`.

**Supprimé (marketplace) :**
`reservations` (consolidé dans `bookings`) · `vehicle_boosts` · `boost_packages`
· `referrals` · `referral_rewards` · `loueur_settings` · `loueur_wilaya`
· `featured_partners` · `social_channels` · Stripe **Connect** · champs commission.

## 4. Machine à états réservation (reprise de ResaDZ)

```
pending → confirmed → active → returning → completed
   │
 expired (timer acompte)   cancelled   dispute
```

## 5. Paiements

- Acompte (avec timer d'expiration) + solde + caution (empreinte/restitution).
- Méthodes : espèces, virement, CIB/Edahabia/BaridiMob, carte en ligne.
- **Pas** de Stripe Connect (split multi-vendeurs) — paiement direct agence.

## 6. Roadmap

**Phase 0 — Nettoyage / fondation**
- Nouveau projet Laravel 11 + Filament 3 (ou fork nettoyé de ResaDZ).
- Schéma : retirer multi-tenant, créer `agency`, `customers`, consolider `bookings`.
- Rôles internes + RBAC.

**Phase 1 — MVP back-office**
- Véhicules · Calendrier (anti double-réservation) · Réservations (cycle complet)
  · Clients · Contrat PDF · Acomptes · Dashboard.

**Phase 2 — Gestion complète**
- Factures · Finance-dashboard / Stats · Tarification dynamique · Messagerie
  · Reviews · Notifications (email/SMS/WhatsApp/push).

**Phase 3 — Mini-site & services**
- Mini-site de réservation à la marque · activation Livraison · activation Transfert/VTC.
