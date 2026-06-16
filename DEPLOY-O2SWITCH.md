# Déploiement sur o2switch (hébergement mutualisé, MySQL/MariaDB, SSH)

> Prérequis : accès SSH (ok), une base MySQL + utilisateur créés dans le cPanel,
> PHP **8.2+** sélectionné pour le sous-domaine.

## 0. Vérifier la version de PHP en CLI

```bash
php -v
```
Si < 8.2, utilise le binaire versionné d'o2switch (ex. `ea-php83`) à la place de `php`
dans toutes les commandes ci-dessous : `ea-php83 artisan ...`, `ea-php83 $(which composer) ...`.

## 1. Cloner le projet (HORS du dossier web, pour la sécurité)

Le code applicatif (`.env`, etc.) ne doit JAMAIS être directement accessible par le web.
On clone dans le home, et on fera pointer le sous-domaine vers `public/`.

```bash
cd ~
git clone -b claude/friendly-darwin-22mavq https://github.com/FaroukHUB/medlicar.git medlicar
cd medlicar
```
> Dépôt privé → GitHub demandera ton identifiant + un **token** (Personal Access Token,
> pas le mot de passe). À créer sur github.com → Settings → Developer settings → Tokens.

## 2. Dépendances PHP (sans dev, optimisé)

```bash
composer install --no-dev --optimize-autoloader
```

## 3. Configurer l'environnement

```bash
cp .env.example .env
nano .env
```
Renseigner (les noms o2switch sont souvent préfixés, ex. `zajr1824_medlicar`) :
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://djiblicar.mon-agenceweb.fr

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zajr1824_medlicar
DB_USERNAME=zajr1824_medlicar
DB_PASSWORD=********
```
Puis :
```bash
php artisan key:generate
```

## 4. Base de données + assets

```bash
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize
```

## 5. Faire pointer le sous-domaine vers `public/`

**Option A — cPanel (recommandé) :** Domaines → sous-domaine `djiblicar.mon-agenceweb.fr`
→ « Racine du document » → `medlicar/public`. Enregistrer.

**Option B — symlink (si tu restes en SSH) :**
```bash
cd ~
rm -rf djiblicar.mon-agenceweb.fr           # ⚠ supprime l'ancien dossier docroot
ln -s medlicar/public djiblicar.mon-agenceweb.fr
```

## 6. Connexion

`https://djiblicar.mon-agenceweb.fr/app` → `owner@medlicar.test` / `password`
**Change le mot de passe immédiatement** (ou recrée un compte owner et supprime celui de démo).

## Mettre à jour le site plus tard

```bash
cd ~/medlicar
git pull origin claude/friendly-darwin-22mavq
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
```
