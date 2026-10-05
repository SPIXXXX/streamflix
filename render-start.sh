#!/bin/sh

set -eu

cd /var/www/html

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [ "${DB_CONNECTION:-}" = "mysql" ] || [ "${DB_CONNECTION:-}" = "mariadb" ]; then
    if [ "${MYSQL_SSL_REQUIRED:-true}" = "true" ]; then
        if [ -z "${MYSQL_ATTR_SSL_CA:-}" ]; then
            echo "MYSQL_ATTR_SSL_CA must point to the Aiven CA certificate." >&2
            exit 1
        fi

        if [ ! -r "$MYSQL_ATTR_SSL_CA" ]; then
            echo "The MySQL CA file at MYSQL_ATTR_SSL_CA is not readable." >&2
            exit 1
        fi
    fi
fi

php artisan migrate --force
php artisan storage:link --force

exec apache2-foreground
