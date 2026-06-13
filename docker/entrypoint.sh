#!/bin/sh
set -e

echo "Starting container startup script..."

# 1. Handle Aiven SSL Certificate if provided via environment variable
if [ -n "$AIVEN_CA_CERT" ]; then
    echo "Aiven CA Certificate detected, writing to file..."
    mkdir -p /var/www/html/certs
    echo "$AIVEN_CA_CERT" > /var/www/html/certs/ca.pem
    chmod 644 /var/www/html/certs/ca.pem
    # Export it so Laravel config/database.php picks it up via env('MYSQL_ATTR_SSL_CA')
    export MYSQL_ATTR_SSL_CA="/var/www/html/certs/ca.pem"
    echo "CA Certificate successfully written to /var/www/html/certs/ca.pem"
fi

# 2. Adjust Nginx listening port based on Railway's dynamic $PORT env variable
if [ -n "$PORT" ]; then
    echo "Setting Nginx to listen on port: $PORT"
    sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/nginx.conf
else
    echo "No dynamic PORT environment variable found, defaulting Nginx to port 80"
fi

# 3. Ensure permissions are correct for Storage and Cache
echo "Setting storage and bootstrap/cache permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 4. Clear old caches and re-cache for production performance
echo "Caching Laravel configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Run Database Migrations
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Running database migrations..."
    # --force is required in production
    php artisan migrate --force
fi

# 6. Start Supervisor to manage PHP-FPM and Nginx
echo "Starting Supervisor..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
