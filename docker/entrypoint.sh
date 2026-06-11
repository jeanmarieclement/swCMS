#!/bin/sh
set -e

cd /var/www/html

# Install PHP dependencies if vendor/ is missing (fresh clone scenario)
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] vendor/ not found, running composer install..."
    composer install --no-interaction --no-progress --prefer-dist
fi

# Ensure writable directories exist and are owned by www-data.
# Apache runs as www-data; the bind-mounted volume is typically owned by
# the host user, so these dirs must be explicitly created/fixed.
for dir in data logs storage/cache app/views/compiled app/views/cache app/views/configs; do
    mkdir -p "$dir"
    chown -R www-data:www-data "$dir"
    chmod 755 "$dir"
done

# Ensure .env is writable by www-data so the installer can create it.
# touch is safe: creates if absent, preserves content if present.
touch .env
chown www-data:www-data .env
chmod 600 .env

exec apache2-foreground
