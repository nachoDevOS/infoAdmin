#!/bin/sh
set -e

cd /var/www/html

# ── 1. Ensure .env exists ─────────────────────────────────────────────────────
if [ ! -f .env ]; then
    cp .env.example .env
fi

# ── 2. Generate APP_KEY if missing ────────────────────────────────────────────
if ! grep -qE '^APP_KEY=.+' .env; then
    php artisan key:generate --force
fi

# ── 3. Storage symlink ────────────────────────────────────────────────────────
php artisan storage:link --force 2>/dev/null || true

# ── 4. Wait for DB (max 60 s), then continue regardless ──────────────────────
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
echo "[entrypoint] Waiting for ${DB_HOST}:${DB_PORT} (max 60s)..."
WAITED=0
until nc -z "${DB_HOST}" "${DB_PORT}" 2>/dev/null || [ "${WAITED}" -ge 60 ]; do
    sleep 2
    WAITED=$((WAITED + 2))
done

if nc -z "${DB_HOST}" "${DB_PORT}" 2>/dev/null; then
    echo "[entrypoint] Database ready. Running migrations..."
    php artisan migrate --force || echo "[entrypoint] WARNING: migrations failed, continuing anyway."
else
    echo "[entrypoint] WARNING: database unreachable after 60s, skipping migrations."
fi

# ── 5. Cache config/routes/views in production ────────────────────────────────
if [ "${APP_ENV}" = "production" ]; then
    php artisan config:cache  || true
    php artisan route:cache   || true
    php artisan view:cache    || true
fi

# ── 6. Create supervisor log directory ────────────────────────────────────────
mkdir -p /var/log/supervisor

echo "[entrypoint] Starting supervisord..."

# ── 7. Hand off to supervisord ────────────────────────────────────────────────
exec /usr/bin/supervisord -c /etc/supervisord.conf
