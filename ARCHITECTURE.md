# Medlicar — Conception du produit

> ERP / CRM spécialisé pour **une seule agence de location de voitures**.
> Inspiré de l'expérience ResaDZ, mais débarrassé de toute logique marketplace / multi-loueurs.

---

## 1. De ResaDZ à Medlicar : ce qu'on garde, ce qu'on supprime

ResaDZ était une **marketplace** : plusieurs loueurs, plusieurs clients, mise en relation,
commissions, partage de revenus, concurrence entre agences. Beaucoup de sa complexité
servait uniquement à arbitrer cette relation N loueurs ↔ N clients.

Medlicar est un **logiciel métier interne** : une entreprise, ses voitures, ses clients,
son équipe. On récupère toute la valeur opérationnelle et on jette la couche marketplace.

### On conserve (cœur de valeur, toujours pertinent en mono-agence)

| Domaine | Pourquoi ça reste |
|---|---|
| Dashboard pro | Pilotage temps réel de l'activité |
| Gestion véhicules | Le parc est l'actif central de l'agence |
| Calendrier de disponibilité | Anti double-réservation, vision du parc |
| Réservations | Cycle de vie complet du contrat de location |
| Contrats PDF | Obligation légale + image pro |
| Acomptes / paiements | Trésorerie, suivi des soldes |
| Statistiques | Décision : quels véhicules, quelle saison, quel taux d'occupation |
| Notifications | Ne rien rater (retours, retards, paiements) |
| Gestion clients | Fidélisation, historique, scoring |
| Tarifs & périodes | Pricing journalier / hebdo / mensuel, saisonnalité |
| UX moderne | Confiance immédiate, adoption par l'équipe |

### On supprime (spécifique marketplace)

- ❌ Marketplace / vitrine publique multi-loueurs
- ❌ Notion de « loueur » comme entité : il n'y a qu'**une** agence
- ❌ Profils loueurs, onboarding loueur, KYC loueur
- ❌ Commissions, prélèvement de plateforme
- ❌ Partage / répartition des revenus
- ❌ Mise en relation, classement concurrentiel, avis inter-agences
- ❌ Multi-tenant au sens SaaS public (single-tenant assumé)

### Ce qui se transforme

- Le « compte loueur » devient un **compte agence unique** avec **plusieurs utilisateurs internes** (rôles : propriétaire, gestionnaire, agent comptoir, comptable).
- Le « catalogue public » devient (optionnellement) un **mini-site de réservation** propre à l'agence (sa marque, son domaine), pas une place de marché.
- La « commission » disparaît ; à la place : **suivi de marge et de rentabilité par véhicule**.

---

## 2. Architecture technique

### Principe directeur

Single-tenant, monolithe modulaire. Pas de microservices : une agence = volume modéré,
la simplicité opérationnelle prime. On structure le code en **modules métier** clairs
pour pouvoir extraire plus tard si besoin.

```
┌──────────────────────────────────────────────────────────────┐
│                        Client (Browser)                       │
│   Next.js App Router · React · Tailwind · shadcn/ui           │
│   Back-office (équipe)        Mini-site réservation (clients)  │
└───────────────┬──────────────────────────────┬───────────────┘
                │ Server Actions / Route Handlers (API)          │
┌───────────────┴──────────────────────────────┴───────────────┐
│                     Couche application                        │
│  Modules : Vehicles · Bookings · Customers · Contracts ·      │
│            Payments · Calendar · Pricing · Stats · Notifs     │
│  Services transverses : Auth/RBAC · Audit · Files · PDF · Jobs│
└───────────────┬───────────────────────────────────────────────┘
                │ Prisma ORM
┌───────────────┴───────────────────────────────────────────────┐
│  PostgreSQL  │  Object Storage (R2/S3)  │  Queue (jobs/cron)   │
└────────────────────────────────────────────────────────────────┘
```

### Couches

1. **Présentation** — Next.js (App Router), Server Components par défaut, Client Components
   pour l'interactif (calendrier, formulaires). Deux surfaces : back-office interne et
   mini-site de réservation public.
2. **Application** — logique métier en modules. Server Actions pour les mutations,
   Route Handlers pour les webhooks (paiement) et l'API.
3. **Domaine** — entités et règles (disponibilité, calcul de prix, états de réservation).
4. **Infrastructure** — Postgres via Prisma, stockage objet pour photos & PDF, file de jobs
   pour notifications / relances / génération PDF asynchrone.

### Cycle de vie d'une réservation (machine à états)

```
DRAFT → PENDING → CONFIRMED → CHECKED_OUT → RETURNED → CLOSED
                     │             │
                  CANCELLED     OVERDUE (retard)
```

La transition `CONFIRMED → CHECKED_OUT` génère le contrat PDF et pose le verrou calendrier.
`RETURNED → CLOSED` se fait après inspection + solde des paiements.

---

## 3. Stack recommandée

| Besoin | Choix | Justification |
|---|---|---|
| Framework | **Next.js 15 (App Router) + TypeScript** | SSR + Server Actions, un seul codebase front/back, DX moderne |
| UI | **Tailwind CSS + shadcn/ui + Radix** | Esthétique Linear/Stripe, accessible, rapide à composer |
| State serveur | **TanStack Query** (côté client) + Server Components | Cache, optimistic updates pour le calendrier |
| ORM / DB | **Prisma + PostgreSQL** | Schéma typé, migrations, relations riches |
| Auth | **Auth.js (NextAuth)** ou **Clerk** | Sessions, rôles internes, magic link / OTP |
| Autorisation | **RBAC maison** (CASL optionnel) | Rôles agence : owner/manager/agent/accountant |
| Stockage fichiers | **Cloudflare R2** ou **Supabase Storage** (S3) | Photos véhicules, contrats PDF |
| PDF | **@react-pdf/renderer** (templates) ou **Puppeteer** (HTML→PDF) | Contrats fidèles à la charte |
| Paiement | **Stripe** (+ adaptateur local : CIB/Edahabia si Algérie) | Acomptes, liens de paiement, webhooks |
| Jobs / cron | **Inngest** ou **BullMQ + Redis** | Relances, détection retards, notifications |
| Emails / SMS | **Resend** (email) + **Twilio / Vonage** (SMS), **WhatsApp Cloud API** | Notifications client & interne |
| Temps réel | **Pusher / Ably** ou WebSocket natif | Calendrier live, badge notifications |
| Observabilité | **Sentry** + **PostHog** | Erreurs + analytics produit |
| Déploiement | **Vercel** + Postgres managé (**Neon/Supabase**) | Zéro-ops, previews |
| Tests | **Vitest** + **Playwright** | Unitaire/domaine + E2E parcours réservation |

> Variante « tout-en-un » plus simple à opérer : **Supabase** (Postgres + Auth + Storage + Realtime)
> derrière le même front Next.js. Recommandé si l'équipe veut minimiser l'infra.

---

## 4. Schéma de base de données

Mono-agence : pas de colonne `tenant_id` partout. Une table `agency` à **une seule ligne**
porte les paramètres globaux (raison sociale, logo, conditions, devise, fuseau).

```prisma
// ─── Organisation & accès ───────────────────────────────
model Agency {
  id            String   @id @default(cuid())
  name          String
  legalName     String?
  logoUrl       String?
  address       String?
  phone         String?
  email         String?
  currency      String   @default("DZD")
  timezone      String   @default("Africa/Algiers")
  vatRate       Decimal? @db.Decimal(5,2)
  contractTerms String?  // CGV / conditions par défaut
  createdAt     DateTime @default(now())
  updatedAt     DateTime @updatedAt
}

enum Role { OWNER MANAGER AGENT ACCOUNTANT }

model User {
  id        String   @id @default(cuid())
  email     String   @unique
  name      String
  role      Role     @default(AGENT)
  active    Boolean  @default(true)
  bookings  Booking[] @relation("createdBy")
  createdAt DateTime @default(now())
}

// ─── Parc véhicules ─────────────────────────────────────
enum Fuel { PETROL DIESEL HYBRID ELECTRIC LPG }
enum Gearbox { MANUAL AUTOMATIC }
enum VehicleStatus { AVAILABLE RENTED MAINTENANCE OUT_OF_SERVICE }

model Category {
  id       String    @id @default(cuid())
  name     String    // Économique, SUV, Luxe, Utilitaire…
  vehicles Vehicle[]
}

model Vehicle {
  id            String        @id @default(cuid())
  brand         String
  model         String
  year          Int
  plate         String        @unique
  vin           String?       @unique
  categoryId    String
  category      Category      @relation(fields: [categoryId], references: [id])
  fuel          Fuel
  gearbox       Gearbox
  seats         Int
  mileage       Int           @default(0)
  dailyPrice    Decimal       @db.Decimal(10,2)
  weeklyPrice   Decimal?      @db.Decimal(10,2)
  monthlyPrice  Decimal?      @db.Decimal(10,2)
  deposit       Decimal       @db.Decimal(10,2)   // caution
  status        VehicleStatus @default(AVAILABLE)
  photos        VehiclePhoto[]
  bookings      Booking[]
  blocks        CalendarBlock[]
  maintenances  Maintenance[]
  documents     VehicleDocument[] // assurance, contrôle technique
  createdAt     DateTime      @default(now())
  @@index([status])
}

model VehiclePhoto {
  id        String  @id @default(cuid())
  vehicleId String
  vehicle   Vehicle @relation(fields: [vehicleId], references: [id], onDelete: Cascade)
  url       String
  isCover   Boolean @default(false)
  position  Int     @default(0)
}

model VehicleDocument {
  id        String   @id @default(cuid())
  vehicleId String
  vehicle   Vehicle  @relation(fields: [vehicleId], references: [id], onDelete: Cascade)
  type      String   // INSURANCE, TECHNICAL_INSPECTION, REGISTRATION
  url       String
  expiresAt DateTime?
}

// ─── Clients ────────────────────────────────────────────
model Customer {
  id            String    @id @default(cuid())
  firstName     String
  lastName      String
  email         String?
  phone         String
  address       String?
  birthDate     DateTime?
  licenseNumber String?
  licenseExpiry DateTime?
  idNumber      String?   // CIN / passeport
  documents     CustomerDocument[]
  notes         Note[]
  bookings      Booking[]
  blacklisted   Boolean   @default(false)
  createdAt     DateTime  @default(now())
  @@index([phone])
}

model CustomerDocument {
  id         String   @id @default(cuid())
  customerId String
  customer   Customer @relation(fields: [customerId], references: [id], onDelete: Cascade)
  type       String   // LICENSE, ID, PASSPORT
  url        String
  expiresAt  DateTime?
}

model Note {
  id         String   @id @default(cuid())
  customerId String
  customer   Customer @relation(fields: [customerId], references: [id], onDelete: Cascade)
  authorId   String
  body       String
  createdAt  DateTime @default(now())
}

// ─── Réservations ───────────────────────────────────────
enum BookingStatus { DRAFT PENDING CONFIRMED CHECKED_OUT RETURNED CLOSED CANCELLED OVERDUE }

model Booking {
  id            String        @id @default(cuid())
  reference     String        @unique           // ex: MED-2026-000123
  vehicleId     String
  vehicle       Vehicle       @relation(fields: [vehicleId], references: [id])
  customerId    String
  customer      Customer      @relation(fields: [customerId], references: [id])
  startDate     DateTime
  endDate       DateTime
  pickupLocation String?
  returnLocation String?
  status        BookingStatus @default(PENDING)
  dailyRate     Decimal       @db.Decimal(10,2)  // figé à la création
  totalAmount   Decimal       @db.Decimal(10,2)
  depositAmount Decimal       @db.Decimal(10,2)
  extras        BookingExtra[]
  payments      Payment[]
  contract      Contract?
  inspections   Inspection[]                      // état des lieux départ/retour
  createdById   String
  createdBy     User          @relation("createdBy", fields: [createdById], references: [id])
  createdAt     DateTime      @default(now())
  updatedAt     DateTime      @updatedAt
  @@index([status, startDate])
  @@index([vehicleId, startDate, endDate])
}

model BookingExtra {
  id        String  @id @default(cuid())
  bookingId String
  booking   Booking @relation(fields: [bookingId], references: [id], onDelete: Cascade)
  label     String  // GPS, siège bébé, conducteur additionnel, assurance+
  price     Decimal @db.Decimal(10,2)
  quantity  Int     @default(1)
}

model Inspection {
  id        String   @id @default(cuid())
  bookingId String
  booking   Booking  @relation(fields: [bookingId], references: [id], onDelete: Cascade)
  type      String   // CHECKOUT (départ) | RETURN (retour)
  mileage   Int
  fuelLevel Int      // %
  photos    String[] // URLs
  damages   String?  // description / coût
  createdAt DateTime @default(now())
}

// ─── Calendrier ─────────────────────────────────────────
model CalendarBlock {
  id        String   @id @default(cuid())
  vehicleId String
  vehicle   Vehicle  @relation(fields: [vehicleId], references: [id], onDelete: Cascade)
  startDate DateTime
  endDate   DateTime
  reason    String   // MAINTENANCE, RESERVED, INTERNAL
  @@index([vehicleId, startDate, endDate])
}

model Maintenance {
  id        String   @id @default(cuid())
  vehicleId String
  vehicle   Vehicle  @relation(fields: [vehicleId], references: [id], onDelete: Cascade)
  type      String   // VIDANGE, PNEUS, RÉVISION…
  cost      Decimal? @db.Decimal(10,2)
  date      DateTime
  nextDueAt DateTime?
  notes     String?
}

// ─── Contrats & paiements ───────────────────────────────
model Contract {
  id         String   @id @default(cuid())
  bookingId  String   @unique
  booking    Booking  @relation(fields: [bookingId], references: [id], onDelete: Cascade)
  number     String   @unique
  pdfUrl     String?
  signedAt   DateTime?
  signatureUrl String? // signature électronique du client
  createdAt  DateTime @default(now())
}

enum PaymentType { DEPOSIT BALANCE REFUND EXTRA }
enum PaymentMethod { CASH CARD TRANSFER ONLINE }
enum PaymentStatus { PENDING PAID FAILED REFUNDED }

model Payment {
  id        String        @id @default(cuid())
  bookingId String
  booking   Booking       @relation(fields: [bookingId], references: [id], onDelete: Cascade)
  type      PaymentType
  method    PaymentMethod
  status    PaymentStatus @default(PENDING)
  amount    Decimal       @db.Decimal(10,2)
  reference String?       // ref Stripe / reçu
  paidAt    DateTime?
  createdAt DateTime      @default(now())
}

// ─── Tarification dynamique ─────────────────────────────
model PricingRule {
  id         String   @id @default(cuid())
  name       String   // "Haute saison été", "Promo -3j"
  categoryId String?
  vehicleId  String?
  startDate  DateTime?
  endDate    DateTime?
  minDays    Int?
  modifier   Decimal  @db.Decimal(5,2) // +20% ou -10%
  isPercent  Boolean  @default(true)
  priority   Int      @default(0)
}

// ─── Notifications & audit ──────────────────────────────
model Notification {
  id        String   @id @default(cuid())
  userId    String?  // null = toute l'agence
  type      String   // NEW_BOOKING, RETURN_DUE, OVERDUE, PAYMENT_RECEIVED
  title     String
  body      String
  entityId  String?
  readAt    DateTime?
  createdAt DateTime @default(now())
  @@index([userId, readAt])
}

model AuditLog {
  id        String   @id @default(cuid())
  userId    String?
  action    String   // BOOKING_CREATED, VEHICLE_UPDATED…
  entity    String
  entityId  String
  metadata  Json?
  createdAt DateTime @default(now())
  @@index([entity, entityId])
}
```

**Garde-fou anti double-réservation** : contrainte applicative (vérification de chevauchement
sur `[startDate, endDate]` par véhicule dans une transaction) + idéalement contrainte
d'exclusion Postgres via `tstzrange` + extension `btree_gist` :

```sql
ALTER TABLE "Booking" ADD CONSTRAINT no_overlap
EXCLUDE USING gist (
  "vehicleId" WITH =,
  tstzrange("startDate", "endDate") WITH &&
) WHERE (status IN ('CONFIRMED','CHECKED_OUT'));
```

---

## 5. Modules principaux

### 5.1 Dashboard
KPIs en temps réel : CA (jour/mois), réservations en cours, à venir (7 j), véhicules
disponibles vs loués, taux d'occupation, paiements en attente, retours du jour, retards.
Graphes : CA mensuel, occupation par catégorie. Liste d'actions « à traiter aujourd'hui ».

### 5.2 Véhicules
CRUD complet, galerie photos (drag & drop, photo de couverture), tarifs J/S/M, caution,
documents (assurance/CT avec alerte d'expiration), historique d'entretien, fiche de
rentabilité par véhicule.

### 5.3 Calendrier
Vues jour / semaine / mois, type planning (véhicules en lignes, temps en colonnes).
Disponibilité temps réel, blocage manuel (maintenance), drag pour créer/déplacer une
réservation, code couleur par statut. Anti double-réservation natif.

### 5.4 Réservations
Création guidée (client → véhicule dispo → dates → extras → tarif → acompte),
modification, annulation (avec politique d'annulation), validation, états des lieux
départ/retour avec photos, historique complet.

### 5.5 Clients (CRM)
Fiche 360° : coordonnées, pièces (permis/CIN avec alerte d'expiration), historique de
locations, paiements, contrats, notes internes, liste noire, score de fidélité.

### 5.6 Contrats
Génération PDF automatique à la charte de l'agence (client, véhicule, période, caution,
extras, CGV), numérotation, signature électronique, archivage, renvoi par email/WhatsApp.

### 5.7 Paiements
Acompte + solde, suivi des reçus, multi-méthodes (espèces/carte/virement/en ligne),
liens de paiement Stripe, gestion des cautions (empreinte/restitution), export comptable.

### 5.8 Tarification
Prix J/S/M, règles dynamiques (saison, durée, catégorie, véhicule), promotions,
extras facturables.

### 5.9 Statistiques
CA mensuel, véhicules les plus loués, taux d'occupation global et par catégorie,
durée moyenne de location, panier moyen, évolution des réservations, top clients.

### 5.10 Notifications
Centre de notifications interne + email/SMS/WhatsApp : nouvelle réservation, retour
prévu, retard, paiement reçu, document/assurance qui expire. Préférences par utilisateur.

### 5.11 Administration
Utilisateurs & rôles (RBAC), paramètres agence (logo, CGV, devise, TVA), journal d'audit.

---

## 6. Améliorations vs ResaDZ

1. **Single-tenant assumé** → code plus simple, requêtes plus rapides, pas de fuite de données inter-agences.
2. **Rôles internes fins** (owner/manager/agent/comptable) au lieu d'un compte loueur monolithique.
3. **États de réservation explicites** (machine à états + audit) → traçabilité totale.
4. **Anti double-réservation au niveau base** (contrainte d'exclusion Postgres) et non seulement applicatif.
5. **État des lieux photo** départ/retour intégré au contrat → moins de litiges caution.
6. **Tarification dynamique** par règles (saison/durée) au lieu de prix figés.
7. **Suivi documents** (assurance, contrôle technique, permis) avec alertes d'expiration.
8. **Rentabilité par véhicule** (revenus − entretien) : décision d'achat/revente du parc.
9. **UX 100 % back-office** sans bruit marketplace ; mini-site de réservation optionnel à la marque de l'agence.
10. **Notifications multicanal** (email + SMS + WhatsApp), très adapté au marché local.

---

## 7. Fonctionnalités premium (vraie valeur métier)

- **Signature électronique** du contrat sur tablette au comptoir (et à distance par lien).
- **Mini-site de réservation en marque blanche** : l'agence prend des réservations en ligne 24/7, sans marketplace.
- **Paiement en ligne + empreinte de caution** (pré-autorisation carte).
- **Rappels automatiques** : J-1 départ, jour du retour, relance retard, relance solde impayé.
- **Gestion de flotte avancée** : échéancier d'entretien, alertes vidange/CT/assurance, coût total de possession.
- **Optimisation tarifaire (yield)** : suggérer un prix selon taux d'occupation et demande.
- **App mobile agent** (PWA) : check-in/check-out, photos état des lieux, scan permis (OCR).
- **Intégration comptable** : export FEC / journal de ventes, rapprochement paiements.
- **Carte / tracking** (si véhicules équipés GPS) : localisation, alerte hors-zone.
- **Programme de fidélité** : points, tarifs clients récurrents.
- **Multi-agences (futur)** : si l'entreprise ouvre des points de vente, ajouter une dimension
  `location` interne (≠ marketplace) sans réintroduire la logique multi-loueurs.

---

## 8. Découpage de mise en œuvre (proposition)

1. **MVP** — Auth/rôles, Véhicules, Clients, Réservations, Calendrier, Contrat PDF, Acompte, Dashboard de base.
2. **V1** — Paiements en ligne, Statistiques, Notifications multicanal, États des lieux photo, Tarification dynamique.
3. **V2 (premium)** — Mini-site réservation marque blanche, signature électronique, rappels auto, gestion de flotte, export comptable.
