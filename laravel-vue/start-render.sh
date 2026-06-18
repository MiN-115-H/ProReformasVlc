#!/usr/bin/env sh
set -eu

cd /app

PERSIST_ROOT="${PERSISTENT_STORAGE_PATH:-/var/data/storage}"
if [ -d "${PERSIST_ROOT}" ] && [ -w "${PERSIST_ROOT}" ]; then
    UPLOADS="${PERSIST_ROOT}/app/public"
    echo "[boot] using persistent storage at ${UPLOADS}"
else
    UPLOADS="/app/storage/app/public"
    echo "[boot] persistent storage not writable, falling back to ${UPLOADS}"
fi

mkdir -p "${UPLOADS}/albums" "${UPLOADS}/servicios"

if [ -e "public/storage" ] || [ -L "public/storage" ]; then
    rm -rf "public/storage"
fi
ln -s "${UPLOADS}" "public/storage"

echo "[boot] public/storage => $(readlink public/storage)"

php artisan config:clear || true
php artisan config:cache
php artisan view:cache

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force || echo "[boot] migration warning: continuing startup"
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force || echo "[boot] seeder warning: continuing startup"
fi

exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
