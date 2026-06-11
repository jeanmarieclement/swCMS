#!/bin/sh
set -e

# Install PHP dependencies if vendor/ is missing (fresh clone scenario)
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "[entrypoint] vendor/ not found, running composer install..."
    cd /var/www/html
    composer install --no-interaction --no-progress --prefer-dist
fi

exec apache2-foreground
