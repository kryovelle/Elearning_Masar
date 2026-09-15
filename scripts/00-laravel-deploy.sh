#!/usr/bin/env bash
echo "Running composer..."
composer install --no-dev --working-dir=/var/www/html

echo "Caching config..."
php artisan config:cache
php artisan route:cache

echo "Linking storage..."
php artisan storage:link || echo "Storage link already exists, skipping"


echo "Running migrations..."
php artisan migrate --force

echo "Seeding database..."
php artisan db:seed --force