#!/bin/bash
set -e

# Ensure storage and bootstrap cache directories exist with correct permissions
mkdir -p /var/www/storage/framework/cache /var/www/storage/framework/sessions /var/www/storage/framework/views /var/www/storage/logs
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create storage symlink if it doesn't exist
if [ ! -d "/var/www/public/storage" ]; then
    echo "Creating storage symlink..."
    php artisan storage:link || true
fi

# Clear stale package and service manifests from bootstrap/cache
rm -f /var/www/bootstrap/cache/packages.php /var/www/bootstrap/cache/services.php /var/www/bootstrap/cache/config.php /var/www/bootstrap/cache/routes.php

# Clear and optimize cache
php artisan config:clear || true
php artisan cache:clear || true

exec "$@"
