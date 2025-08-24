#!/bin/bash

# Script to create admin user
# Accept credentials via arguments for robustness (fallback to temp files)
if [ -n "$1" ]; then
    ADMIN_EMAIL="$1"
else
    ADMIN_EMAIL=$(cat .tmp_admin_email 2>/dev/null || true)
fi

if [ -n "$2" ]; then
    ADMIN_PASSWORD="$2"
else
    ADMIN_PASSWORD=$(cat .tmp_admin_password 2>/dev/null || true)
fi

if [ -z "$ADMIN_EMAIL" ] || [ -z "$ADMIN_PASSWORD" ]; then
    echo "❌ Missing admin credentials (email/password)."
    exit 1
fi

echo "👤 Creating admin user with email: $ADMIN_EMAIL"

# Use PHP to create the user via a simple inline script
php -r "
require_once 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    \$existing = App\Models\User::where('email', '$ADMIN_EMAIL')->first();
    if (\$existing) {
        echo '✅ Admin user already exists' . PHP_EOL;
    } else {
        \$user = App\Models\User::create([
            'name' => 'Admin',
            'email' => '$ADMIN_EMAIL',
            'password' => Hash::make('$ADMIN_PASSWORD'),
            'email_verified_at' => now(),
        ]);
        echo '✅ Admin user created successfully: $ADMIN_EMAIL' . PHP_EOL;
    }
} catch (Exception \$e) {
    echo '❌ Error: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"
