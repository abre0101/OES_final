#!/bin/sh
echo "Starting PHP-FPM..."
php-fpm -D
echo "Starting Nginx..."
exec nginx -g "daemon off;"
