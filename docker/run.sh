#!/bin/sh

cd /var/www

php artisan cache:clear
php artisan route:cache
php artisan config:cache

php artisan migrate --seed --force
php artisan scrap:news

# If we have logged to file during migration, make it writable to www
# chown www storage/logs/*

/usr/bin/supervisord -c /etc/supervisord.conf
