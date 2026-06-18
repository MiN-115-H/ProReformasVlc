#!/usr/bin/env sh
set -e

cd /var/www/html

# Optional persistent disk mount path for uploaded files.
if [ -n "${PERSISTENT_STORAGE_PATH:-}" ]; then
  mkdir -p "${PERSISTENT_STORAGE_PATH}/app/public"

  if [ -d "storage/app/public" ] && [ ! -L "storage/app/public" ]; then
    rm -rf "storage/app/public"
  fi

  if [ ! -L "storage/app/public" ]; then
    ln -s "${PERSISTENT_STORAGE_PATH}/app/public" "storage/app/public"
  fi
fi

# Ensure Laravel public storage URL works.
php artisan storage:link || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

apache2-foreground