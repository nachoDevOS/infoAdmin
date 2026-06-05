#!/bin/sh
set -e

# ── 1. Ensure .env exists ─────────────────────────────────────────────────────
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# ── 2. Generate APP_KEY if missing ────────────────────────────────────────────
if ! grep -qE '^APP_KEY=.+' /var/www/html/.env; then
    php artisan key:generate --force
fi

# ── 3. Storage symlink ────────────────────────────────────────────────────────
php artisan storage:link --force 2>/dev/null || true

# ── 4. Wait for the database to be reachable ─────────────────────────────────
DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
echo "[entrypoint] Waiting for ${DB_HOST}:${DB_PORT}..."
until nc -z "${DB_HOST}" "${DB_PORT}" 2>/dev/null; do
    sleep 2
done
echo "[entrypoint] Database ready."

# ── 5. Run migrations ─────────────────────────────────────────────────────────
php artisan migrate --force

# ── 6. Cache config/routes/views in production ────────────────────────────────
if [ "${APP_ENV}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# ── 7. Create supervisor log directory ────────────────────────────────────────
mkdir -p /var/log/supervisor

# ── 8. Hand off to supervisord ────────────────────────────────────────────────
exec /usr/bin/supervisord -c /etc/supervisord.conf
