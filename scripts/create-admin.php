<?php

// Simple script to create admin user via Artisan command
// This avoids complex Laravel bootstrapping

$adminEmail = trim(file_get_contents('.tmp_admin_email'));
$adminPassword = trim(file_get_contents('.tmp_admin_password'));

// Create a temporary Artisan command to create the admin
$command = sprintf(
    'php artisan tinker --execute="' .
        'try { ' .
        '\$existing = App\Models\User::where(\'email\', \'%s\')->first(); ' .
        'if (\$existing) { echo \'✅ Admin already exists\n\'; } else { ' .
        '\$user = new App\Models\User(); ' .
        '\$user->name = \'Admin\'; ' .
        '\$user->email = \'%s\'; ' .
        '\$user->password = Hash::make(\'%s\'); ' .
        '\$user->email_verified_at = now(); ' .
        '\$user->save(); ' .
        'echo \'✅ Admin created: %s\n\'; ' .
        '} } catch (Exception \$e) { echo \'❌ Error: \' . \$e->getMessage() . \'\n\'; }"',
    addslashes($adminEmail),
    addslashes($adminEmail),
    addslashes($adminPassword),
    addslashes($adminEmail)
);

// Execute the command and capture output
$output = shell_exec($command);
echo $output;
