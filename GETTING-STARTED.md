# 🚀 Guide de Démarrage Rapide

## Installation en 5 minutes

### 1. Récupérer le starter kit

```bash
# Option A: Use this template sur GitHub (recommandé)
# Cliquez sur "Use this template" puis clonez votre nouveau repo

# Option B: Clone direct
git clone https://github.com/matthieuLabaune/laravel-12-vue-docker-starter-kit.git mon-projet
cd mon-projet

# Option C: Nouveau repo propre
git clone https://github.com/matthieuLabaune/laravel-12-vue-docker-starter-kit.git mon-projet
cd mon-projet
rm -rf .git
git init
```

### 2. Configuration initiale

```bash
# Copier et personnaliser l'environnement
cp .env.example .env

# Éditer .env avec vos paramètres
nano .env  # ou votre éditeur préféré
```

**Variables importantes à modifier dans `.env` :**
```env
APP_NAME="Mon Super Projet"
APP_URL=http://localhost:8000
DB_DATABASE=mon_projet_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Installation des dépendances

```bash
# Backend PHP
composer install

# Frontend Node.js
npm install
```

### 4. Configuration Laravel

```bash
# Générer la clé d'application
php artisan key:generate

# Créer et configurer la base de données
# Puis lancer les migrations
php artisan migrate
```

### 5. Démarrage

```bash
# Terminal 1: Serveur Laravel
php artisan serve

# Terminal 2: Assets frontend (développement)
npm run dev

# Ou build pour production
npm run build
```

🎉 **Votre application est prête sur [http://localhost:8000](http://localhost:8000)**

## ⚡ Workflow de Développement

### Commandes Essentielles

```bash
# Développement
npm run dev              # Hot reload frontend
php artisan serve        # Serveur Laravel

# Tests
php artisan test         # Tests Pest
npm run lint            # ESLint

# Production
npm run build           # Build optimisé
php artisan optimize    # Cache Laravel
```

### Création de Nouvelles Fonctionnalités

```bash
# Backend
php artisan make:controller MonController --resource
php artisan make:model MonModel -m
php artisan make:request MonFormRequest

# Frontend
# Créer resources/js/pages/MaPage.vue
# Utiliser les composants Shadcn-vue disponibles
```

### Structure du Projet

```
resources/js/
├── components/        # Composants réutilisables
├── components/ui/     # Composants Shadcn-vue
├── pages/            # Pages Inertia (routes)
├── layouts/          # Layouts Vue
├── composables/      # Logique réutilisable
└── types/           # Types TypeScript

app/Http/
├── Controllers/      # Contrôleurs Laravel
├── Requests/        # Validation des formulaires
└── Middleware/      # Middleware personnalisés
```

## 🔧 Personnalisation Rapide

### 1. Branding
- Logo : `public/favicon.svg`
- Nom app : `.env` → `APP_NAME`
- Couleurs : `resources/css/app.css`

### 2. Navigation
- Menu : `resources/js/components/NavMain.vue`
- Routes : `routes/web.php`

### 3. Base de données
- Models : `app/Models/`
- Migrations : `database/migrations/`

## 📚 Ressources

- [Laravel Docs](https://laravel.com/docs)
- [Inertia.js Docs](https://inertiajs.com/)
- [Vue 3 Docs](https://vuejs.org/)
- [Shadcn-vue Components](https://www.shadcn-vue.com/)
- [Tailwind CSS](https://tailwindcss.com/)

## 🆘 Aide

- **Copilot Instructions** : Consultez `copilot-instructions.md` pour les bonnes pratiques
- **Tests** : Exemples dans `tests/Feature/` et `tests/Unit/`
- **Issues** : [GitHub Issues](https://github.com/matthieuLabaune/laravel-12-vue-docker-starter-kit/issues)

---

**Bon développement ! 🎊**
