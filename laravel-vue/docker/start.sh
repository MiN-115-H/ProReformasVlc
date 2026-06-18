#!/usr/bin/env sh
set -e

cd /var/www/html

echo "=== ProReformasVLC boot ==="

# ---------------------------------------------------------------
# 1. Persistent disk: make sure upload directories exist
# ---------------------------------------------------------------
UPLOADS_DIR="${PERSISTENT_STORAGE_PATH:-/var/data/storage}/app/public"
mkdir -p "${UPLOADS_DIR}/albums"
mkdir -p "${UPLOADS_DIR}/servicios"
echo "[storage] uploads dir: ${UPLOADS_DIR}"

# ---------------------------------------------------------------
# 2. Point storage/app/public at the persistent disk
#    (Laravel uses this path when storing files)
# ---------------------------------------------------------------
if [ -d "storage/app/public" ] && [ ! -L "storage/app/public" ]; then
  rm -rf "storage/app/public"
fi
if [ ! -L "storage/app/public" ]; then
  ln -s "${UPLOADS_DIR}" "storage/app/public"
fi
echo "[storage] storage/app/public -> $(readlink storage/app/public)"

# ---------------------------------------------------------------
# 3. Point public/storage DIRECTLY at the persistent disk
#    (skip the double-symlink that confuses Apache)
# ---------------------------------------------------------------
if [ -d "public/storage" ] && [ ! -L "public/storage" ]; then
  rm -rf "public/storage"
fi
if [ -L "public/storage" ]; then
  rm "public/storage"
fi
ln -s "${UPLOADS_DIR}" "public/storage"
echo "[storage] public/storage -> $(readlink public/storage)"

# ---------------------------------------------------------------
# 4. Permissions
# ---------------------------------------------------------------
chown -R www-data:www-data "${UPLOADS_DIR}" || true
chmod -R 775 "${UPLOADS_DIR}" || true

# ---------------------------------------------------------------
# 5. Database
# ---------------------------------------------------------------
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  php artisan db:seed --force
fi

# ---------------------------------------------------------------
# 6. Laravel cache
# ---------------------------------------------------------------
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ---------------------------------------------------------------
# 7. Smoke test
# ---------------------------------------------------------------
echo "[storage] contents of uploads dir:"
ls -la "${UPLOADS_DIR}" || echo "(empty)"
echo "[storage] public/storage resolves to: $(realpath public/storage 2>/dev/null || echo 'ERROR')"
echo "=== Boot complete, starting Apache ==="

apache2-foreground