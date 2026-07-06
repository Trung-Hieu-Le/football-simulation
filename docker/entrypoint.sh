#!/bin/sh
set -e

cd /var/www/html

if [ ! -f /var/www/html/vendor/autoload.php ]; then
  echo "Installing Composer dependencies..."
  composer install --no-interaction --prefer-dist --no-progress
fi

exec "$@"
