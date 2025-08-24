# laravel-12-vue-docker-starter-kit

Un kit de démarrage moderne avec Laravel 12, Inertia.js, Vue 3, et une configuration Docker complète, optimisé pour le développement avec GitHub Copilot.

## 🚀 Stack Technique

- **Backend**: Laravel 12 avec PHP 8.4
- **Frontend**: Inertia.js v2 + Vue 3 + TypeScript
- **Styling**: Tailwind CSS v4
- **Testing**: Pest v4
- **Database**: MySQL 8.0
- **Containerization**: Docker avec Docker Compose
- **Task Runner**: Taskfile (Go Task)
- **Development**: Vite, Hot Reload
- **Email**: Mailpit pour les tests d'emails
- **AI**: Configuration GitHub Copilot incluse

## 📦 Services Docker

- **app-php**: PHP 8.4-FPM avec extensions Laravel
- **app-nginx**: Serveur web Nginx
- **app-mysql**: Base de données MySQL 8.0
- **app-node**: Node.js pour Vite et assets
- **app-queue**: Worker pour les queues Laravel
- **app-scheduler**: Cron scheduler Laravel
- **app-mailpit**: Interface web pour les emails de test

## 🚀 Créer un Nouveau Projet

### ⚡ **Méthode Magique : Une Seule Commande !**

```bash
# 🎯 Installation automatique complète
task new
```

Cette commande interactive va :
- ✅ Créer la structure du projet
- ✅ Configurer Docker (PHP, Nginx, MySQL, Node, Mailpit)
- ✅ Installer toutes les dépendances
- ✅ Configurer la base de données + migrations
- ✅ Créer l'utilisateur administrateur
- ✅ Initialiser Git + optionnel GitHub repo
- ✅ Démarrer tous les services

**Résultat** : Projet prêt en 5 minutes sur [http://localhost](http://localhost) ! 🎉

> 📚 **Guide détaillé** : Consultez [TASKFILE-GUIDE.md](./TASKFILE-GUIDE.md)

### 🛠 **Méthodes Alternatives**

### **Méthode 1: Use this template**

1. **Utiliser GitHub Template**
   ```bash
   # Cliquez sur "Use this template" sur GitHub
   # Ou clonez directement :
   git clone https://github.com/matthieuLabaune/laravel-12-vue-docker-starter-kit.git mon-nouveau-projet
   cd mon-nouveau-projet
   ```

2. **Nettoyer l'historique Git (optionnel)**
   ```bash
   rm -rf .git
   git init
   git add .
   git commit -m "feat: initial commit from starter kit"
   ```

3. **Configuration initiale**
   ```bash
   # Copier le fichier d'environnement
   cp .env.example .env

   # Éditer .env avec vos paramètres (DB, APP_NAME, etc.)
   nano .env
   ```

### **Méthode 2: Installation Locale** (sans Docker)

```bash
# 1. Installer les dépendances PHP
composer install

# 2. Générer la clé d'application
php artisan key:generate

# 3. Configurer la base de données dans .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=votre_base
# DB_USERNAME=votre_user
# DB_PASSWORD=votre_password

# 4. Lancer les migrations
php artisan migrate

# 5. Installer les dépendances frontend
npm install

# 6. Builder les assets
npm run build

# 7. Démarrer le serveur de développement
php artisan serve
```

### **Méthode 3: Avec Task/Docker** (Manuel)

```bash
# Installation manuelle avec les outils inclus
cp .env.example .env
task up              # Démarrer tous les services
task a -- key:generate
task a -- migrate    # Lancer les migrations
task npm -- install  # Installer les dépendances
```

**Applications accessibles :**
- 🌐 **Principal** : [http://localhost](http://localhost)
- 📧 **Emails** : [http://localhost:8025](http://localhost:8025) (Mailpit)
- 🗃️ **Base de données** : `localhost:3306`

## ⚙️ Personnalisation du Projet

### 1. Configuration de Base

```bash
# Dans .env, personnalisez :
APP_NAME="Mon Super Projet"
APP_URL=http://localhost:8000
DB_DATABASE=mon_projet_db
```

### 2. Personnalisation de l'Interface

```bash
# Logo et branding
# Remplacez les fichiers dans public/ :
# - favicon.ico
# - favicon.svg
# - apple-touch-icon.png

# Couleurs et thème
# Modifiez resources/css/app.css pour les couleurs personnalisées
```

### 3. Ajout de Fonctionnalités

```bash
# Créer un nouveau module
php artisan make:controller MonController --resource
php artisan make:model MonModel -m
php artisan make:request MonFormRequest

# Ajouter une nouvelle page Vue
# Créez le fichier dans resources/js/pages/MaPage.vue
```

### 4. Configuration GitHub Copilot

Ce starter kit inclut `copilot-instructions.md` avec toutes les bonnes pratiques pour :
- Architecture Laravel + Inertia + Vue
- Patterns de développement recommandés
- Standards de code TypeScript/PHP
- Utilisation de Shadcn-vue et Tailwind CSS

## 🛠 Commandes de Développement

### Installation Locale
```bash
# Développement frontend
npm run dev          # Mode développement avec hot reload
npm run build        # Build de production
npm run lint         # Vérification ESLint
npm run format       # Formatage Prettier

# Backend Laravel
php artisan serve    # Serveur de développement
php artisan migrate  # Migrations
php artisan test     # Tests Pest
./vendor/bin/pint    # Formatage PHP
```

### Avec Docker (à venir)
```bash
# Ces commandes nécessitent d'ajouter Taskfile.yml et docker-compose.yml
task up          # Démarrer tous les services
task down        # Arrêter tous les services
task a -- migrate # Lancer les migrations
task test        # Lancer les tests
task pint        # Formatage PHP
```

## 🎨 Fonctionnalités Incluses
```
task npm -- cmd="run build"        # Build de production
task npm -- cmd="install"          # Installer les dépendances
```

### Tests & Qualité
```bash
task test                          # Lancer tous les tests
task test -- --filter=MonTest     # Lancer des tests spécifiques
task pint                          # Formater le code PHP
```

## 🎨 Fonctionnalités Incluses

### Interface Utilisateur
- ✅ Layout responsive avec sidebar
- ✅ Mode sombre/clair
- ✅ Composants UI de base
- ✅ Navigation avec Inertia.js
- ✅ Icons avec Heroicons

### Authentification
- ✅ Système d'auth Laravel (login/register)
- ✅ Middleware de protection des routes
- ✅ Gestion des sessions

### Internationalisation
- ✅ Support français/anglais
- ✅ Traductions dynamiques côté client

### Développement
- ✅ Hot reload avec Vite
- ✅ TypeScript configuré
- ✅ ESLint + Prettier
- ✅ Instructions Copilot optimisées

## 📄 Licence

MIT License

---

**Fait avec ❤️ pour des démarrages de projets rapides et efficaces**
