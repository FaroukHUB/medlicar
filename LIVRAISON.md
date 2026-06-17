# Guide de livraison — Template MedliCar

Ce projet est un **template SaaS mono-agence** : on déploie **une instance par client**
(1 code + 1 base de données + 1 domaine). Tout le branding et le contenu sont
administrables, **aucun code à modifier** pour un nouveau client.

---

## 1. Modèle

```
1 agence cliente = 1 instance = 1 base de données = 1 domaine
```

Avantages : isolation totale des données, personnalisation illimitée, domaine propre.

---

## 2. Déployer une nouvelle agence (≈ 1 à 2 h)

### a. Cloner le code
```bash
git clone <repo> agence-client && cd agence-client
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

### b. Configurer `.env` (spécifique au client)
```dotenv
APP_NAME="Nom du client"
APP_URL=https://www.domaine-client.dz
DB_DATABASE=agence_client
DB_USERNAME=...
DB_PASSWORD=...
# Email (pour notifications) :
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_USERNAME=...
MAIL_PASSWORD=...
```

### c. Préparer la base et l'instance
```bash
php artisan migrate --force
php artisan agency:provision --name="Nom du client" --email=admin@client.dz --password=motdepasse
# Ajouter --demo pour pré-remplir avec du contenu d'exemple :
# php artisan agency:provision ... --demo
php artisan optimize
```

> `agency:provision` crée l'administrateur, fixe le nom + les couleurs, et fait `storage:link`.

### d. Pointer le domaine
- DNS du client → IP du serveur.
- Vhost / SSL (Let's Encrypt) sur `APP_URL`.

---

## 3. Personnaliser depuis l'admin (`/app`)

Tout se fait dans **Configuration → Paramètres** :

| Onglet | À renseigner |
|---|---|
| **Identité** | nom, logo, slogan, téléphone, WhatsApp, email, adresse |
| **Apparence du site** | couleur primaire + secondaire, favicon, image de partage |
| **Réseaux sociaux** | Facebook, Instagram, TikTok |
| **Référencement (SEO)** | titre SEO, meta description, mots-clés |
| **Sections** | activer/désactiver et renommer chaque bloc de la page d'accueil |

Puis le contenu :
- **Site web → Bannières** : le slider d'accueil.
- **Site web → Pourquoi nous / FAQ / Statistiques** : les blocs de la vitrine.
- **Marketing → Avantages** : le catalogue d'avantages affichés sur les voitures.
- **Catalogue → Mes Véhicules** : la flotte (cocher avantages, coup de cœur, promo).

> Changer les **2 couleurs** suffit à re-brander **tout le site ET l'admin**.

---

## 4. Remettre une instance à blanc (avant livraison)

Pour livrer un site vierge (après les tests / la démo) :
```bash
php artisan agency:reset-content --force
```
Supprime : véhicules, réservations, clients, avis, bannières, avantages, FAQ, stats.
Conserve : utilisateurs, paramètres de l'agence, catégories, marques.

---

## 5. Checklist go-live

- [ ] `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` = domaine final
- [ ] `php artisan migrate --force`
- [ ] Branding complet dans l'admin (logo, couleurs, coordonnées)
- [ ] Flotte saisie + véhicules **Actifs**
- [ ] SEO renseigné
- [ ] `php artisan storage:link`
- [ ] `php artisan optimize`
- [ ] SSL actif sur le domaine
