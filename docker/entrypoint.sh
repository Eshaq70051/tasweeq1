#!/bin/sh
set -e

echo "==> Preparing Laravel for production..."

# Create storage symlink
php artisan storage:link --force || true

# Optimize cache
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Migrate database
echo "==> Running database migrations..."
php artisan migrate --force || true

# Start PHP-FPM daemon
echo "==> Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground
echo "==> Starting Nginx on port 80..."
exec nginx -g "daemon off;"
