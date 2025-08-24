#!/usr/bin/env bash
set -euo pipefail

# Install composer deps if vendor missing
if [ ! -d vendor ]; then
  echo "📦 Installing Composer deps..."
  composer install
fi

# Install node deps if node_modules missing
if [ ! -d node_modules ]; then
  echo "🧩 Installing Node deps..."
  npm install
fi

# Run migrations if not migrated (simple check on users table)
if php artisan migrate:status >/dev/null 2>&1; then
  :
else
  echo "🗄️  Running migrations..."
  php artisan migrate --force || true
fi

# Start Vite dev (background) & php-fpm
if [ "${VITE_DISABLED:-0}" = "1" ]; then
  echo "⚙️  Vite disabled (VITE_DISABLED=1)"
else
  echo "⚡ Starting Vite dev server..."
  (npm run dev -- --host 0.0.0.0 &)
fi

echo "🐘 Starting PHP-FPM..."
exec php-fpm
