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
# Remove stale directory or broken symlink before linking.
if [ -d "public/storage" ] && [ ! -L "public/storage" ]; then
  rm -rf public/storage
fi
if [ -L "public/storage" ] && [ ! -e "public/storage" ]; then
  rm public/storage
fi
php artisan storage:link || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  php artisan db:seed --force
fi

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Storage path: $(php -r 'echo storage_path("app/public");')"
echo "Public storage link: $(php -r 'echo public_path("storage");')"

apache2-foreground