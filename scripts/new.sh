#!/usr/bin/env bash
set -euo pipefail

# --- Configuration defaults ---
DEFAULT_PROJECT_NAME=${DEFAULT_PROJECT_NAME:-my-laravel-app}
DEFAULT_DB_NAME=${DEFAULT_DB_NAME:-laravel_app}
DEFAULT_ADMIN_EMAIL=${DEFAULT_ADMIN_EMAIL:-admin@example.com}

printf "Project name (%s): " "$DEFAULT_PROJECT_NAME"; read PROJECT_NAME; PROJECT_NAME=${PROJECT_NAME:-$DEFAULT_PROJECT_NAME}
if [[ ! "$PROJECT_NAME" =~ ^[a-zA-Z0-9_-]+$ ]]; then
  echo "❌ Invalid project name"; exit 1; fi
printf "Database name (%s): " "$DEFAULT_DB_NAME"; read DB_NAME; DB_NAME=${DB_NAME:-$DEFAULT_DB_NAME}
printf "Admin email (%s): " "$DEFAULT_ADMIN_EMAIL"; read ADMIN_EMAIL; ADMIN_EMAIL=${ADMIN_EMAIL:-$DEFAULT_ADMIN_EMAIL}
printf "Admin password: "; stty -echo; read ADMIN_PASSWORD; stty echo; echo ""; [ -z "$ADMIN_PASSWORD" ] && echo "❌ Password required" && exit 1

printf "Create GitHub repository? (y/N): "; read CREATE_REPO
if [[ "$CREATE_REPO" =~ ^[Yy]$ ]]; then
  printf "GitHub repository name (%s): " "$PROJECT_NAME"; read REPO_NAME; REPO_NAME=${REPO_NAME:-$PROJECT_NAME}
  printf "Visibility (public/private) [private]: "; read REPO_VISIBILITY; REPO_VISIBILITY=${REPO_VISIBILITY:-private}
fi

cat <<SUMMARY
\nConfiguration Summary
---------------------
Project: $PROJECT_NAME
Database: $DB_NAME
Admin:   $ADMIN_EMAIL
$( [[ "$CREATE_REPO" =~ ^[Yy]$ ]] && echo "Repo:    $REPO_NAME ($REPO_VISIBILITY)" )
SUMMARY

printf "Proceed? (Y/n): "; read CONFIRM; [[ "$CONFIRM" =~ ^[Nn]$ ]] && echo "Cancelled" && exit 0

# Create directory
TARGET_DIR="../$PROJECT_NAME"; [ -d "$TARGET_DIR" ] && echo "❌ Directory exists" && exit 1
mkdir "$TARGET_DIR"

rsync -a --exclude='.git/' --exclude='node_modules/' --exclude='vendor/' --exclude='.env' ./ "$TARGET_DIR/"
cd "$TARGET_DIR"

cp .env.example .env
sed -i.bak "s/APP_NAME=Laravel/APP_NAME=\"$PROJECT_NAME\"/" .env; sed -i.bak "s/DB_DATABASE=laravel/DB_DATABASE=$DB_NAME/" .env; rm -f .env.bak

# If docker-compose.yml not present, copy from template root if exists
if [ ! -f docker-compose.yml ] && [ -f ../laravel-inertia-docker-starter/docker-compose.yml ]; then
  cp ../laravel-inertia-docker-starter/docker-compose.yml .
fi

if ! docker info >/dev/null 2>&1; then echo "❌ Docker not running"; exit 1; fi

echo "🐳 Starting containers..."; docker-compose up -d --build
sleep 10

echo "📦 Installing PHP deps..."; docker-compose exec -T app composer install

echo "🔐 App key..."; docker-compose exec -T app php artisan key:generate

echo "🗄️  Migrations..."; docker-compose exec -T app php artisan migrate

echo "👤 Admin user..."; docker-compose exec -T app bash scripts/create-admin.sh "$ADMIN_EMAIL" "$ADMIN_PASSWORD"

echo "🎨 Node deps..."; docker-compose exec -T node npm install || true

echo "🔄 Git init..."; git init; git add .; git commit -m "Initial commit"

if [[ "$CREATE_REPO" =~ ^[Yy]$ ]] && command -v gh >/dev/null 2>&1; then
  if [[ "$REPO_VISIBILITY" == public ]]; then visFlag="--public"; else visFlag="--private"; fi
  gh repo create "$REPO_NAME" $visFlag --source=. --push || echo "⚠️ GitHub repo creation skipped"
fi

echo -e "\n🎉 Done!\ncd $PROJECT_NAME\n task dev"
