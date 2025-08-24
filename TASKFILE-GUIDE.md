# 🚀 Guide Taskfile - Création Automatique de Projet

Ce Taskfile permet de créer automatiquement un nouveau projet Laravel complet en une seule commande !

## 📋 Prérequis

- [Task](https://taskfile.dev/installation/) installé
- Docker et Docker Compose
- Git configuré
- [GitHub CLI](https://cli.github.com/) (optionnel, pour la création automatique de repo)

## ⚡ Utilisation Rapide

```bash
# Dans le dossier du starter kit
task new
```

Cette commande va vous demander interactivement :

1. **📁 Nom du projet** (ex: `mon-super-projet`)
2. **📋 Titre de l'application** (ex: `Mon Super Projet`)
3. **👤 Nom de l'administrateur**
4. **📧 Email de l'administrateur**
5. **🔐 Mot de passe administrateur** (min 8 caractères)
6. **🐙 Création automatique du repo GitHub** (y/N)
7. **👤 Username GitHub** (si création auto)

## 🎯 Ce que fait la commande `task new`

### 1. 📁 **Structure du Projet**
- Copie tout le starter kit dans `../nom-du-projet`
- Nettoie l'historique Git
- Met à jour le README avec le nom du projet

### 2. ⚙️ **Configuration**
- Génère le fichier `.env` personnalisé
- Configure le nom de l'application
- Configure la base de données

### 3. 🐳 **Docker Setup**
- Crée `docker-compose.yml`
- Génère les Dockerfiles (PHP, Nginx)
- Configure tous les services :
  - **PHP 8.4-FPM** avec extensions Laravel
  - **Nginx** serveur web
  - **MySQL 8.0** base de données
  - **Node 20** pour Vite/assets
  - **Mailpit** pour les emails de test

### 4. 📦 **Installation**
- Démarre tous les containers Docker
- Installe les dépendances Composer
- Génère la clé Laravel (`APP_KEY`)
- Installe les dépendances npm

### 5. 🗃️ **Base de Données**
- Attend que MySQL soit prêt
- Lance les migrations automatiquement
- Crée l'utilisateur administrateur

### 6. 🐙 **Git & GitHub**
- Initialise un nouveau repository Git
- Crée le commit initial
- **Optionnel** : Crée automatiquement le repo GitHub (avec GitHub CLI)

## 🎉 **Résultat Final**

Après `task new`, vous obtenez :

```
../mon-super-projet/
├── 🌐 Application accessible sur http://localhost
├── 👤 Utilisateur admin créé et prêt
├── 📱 Interface d'emails sur http://localhost:8025
├── 🐳 Tous les containers Docker démarrés
├── 🗃️ Base de données migrée
└── 🐙 Repository Git initialisé (+ GitHub si demandé)
```

## 🛠 **Commandes de Développement**

Une fois dans votre nouveau projet :

```bash
cd ../mon-super-projet

# Développement quotidien
task dev          # Démarre avec hot reload
task up           # Démarre tous les services
task down         # Arrête tous les services

# Laravel
task a -- migrate        # Migrations
task a -- make:model Mon  # Créer modèle
task tinker              # Shell Laravel

# Frontend
task npm -- run dev     # Hot reload
task npm -- run build   # Build production

# Tests & Qualité
task test               # Tests Pest
task pint              # Formatage PHP

# Maintenance
task logs              # Voir les logs
task shell            # Shell dans container PHP
task clean            # Nettoyer tout
```

## 📚 **Exemple Complet**

```bash
# 1. Dans le starter kit
task new

# Dialogue interactif :
# 📁 Nom du projet: blog-entreprise
# 📋 Titre: Blog de l'Entreprise
# 👤 Admin: Jean Dupont
# 📧 Email: admin@entreprise.com
# 🔐 Password: ********
# 🐙 GitHub repo: y
# 👤 Username: monentreprise

# 2. Résultat automatique
# ✅ Projet créé dans ../blog-entreprise
# ✅ Docker démarré sur http://localhost
# ✅ User admin créé: admin@entreprise.com
# ✅ GitHub repo: github.com/monentreprise/blog-entreprise

# 3. Commencer le développement
cd ../blog-entreprise
task dev    # Hot reload frontend + backend
```

## 🔧 **Configuration Avancée**

### Variables d'Environnement

Le `.env` généré inclut :
```env
APP_NAME="Mon Super Projet"
APP_URL=http://localhost
DB_DATABASE=mon_super_projet
# + configuration Docker optimisée
```

### Services Docker

- **app-php** : `http://localhost` (via Nginx)
- **app-mysql** : `localhost:3306`
- **app-node** : `localhost:5173` (Vite dev server)
- **app-mailpit** : `http://localhost:8025`

### GitHub CLI Setup

Pour la création automatique de repos :
```bash
# Installer GitHub CLI
brew install gh  # macOS
# ou apt install gh  # Ubuntu

# Se connecter
gh auth login
```

## ❓ **Dépannage**

### Docker non accessible
```bash
# Vérifier Docker
docker --version
docker compose --version

# Redémarrer Docker Desktop si nécessaire
```

### Permissions fichiers
```bash
# Si problèmes de permissions
sudo chown -R $(whoami) ../mon-projet
```

### Port déjà utilisé
```bash
# Vérifier les ports occupés
lsof -i :80     # Nginx
lsof -i :3306   # MySQL

# Modifier les ports dans docker-compose.yml si nécessaire
```

## 🎊 **Et voilà !**

En une seule commande `task new`, vous avez un projet Laravel complet, dockerisé, avec un admin prêt à l'emploi et tout l'environnement de développement configuré !

---

**Pro tip** : Gardez ce starter kit dans un dossier dédié et utilisez `task new` à chaque nouveau projet ! 🚀
