# ─── Stage 1: Node – compile frontend assets ─────────────────────────────────
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package.json ./
RUN npm install --ignore-scripts

COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/   ./public/

RUN npm run build

# ─── Stage 2: PHP production image ───────────────────────────────────────────
FROM php:8.3-fpm-alpine

LABEL maintainer="InfoAdmin"

# Runtime libs + build-time deps for PHP extensions (all in one layer to keep image small)
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        unzip \
        netcat-openbsd \
        # runtime shared libs needed by PHP extensions
        oniguruma \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        icu-libs \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        oniguruma-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        pcntl \
        bcmath \
        gd \
        opcache \
        intl \
        exif \
    && apk del --no-network .build-deps

# Install Composer from its official image
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies (no dev, no scripts, no autoloader yet)
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

# Copy the full application
COPY . .

# Overlay the compiled frontend assets from the node stage
COPY --from=node-builder /app/public/build ./public/build

# Generate optimised autoload and discover packages
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan package:discover --ansi 2>/dev/null || true

# Ensure all storage/cache directories exist and have correct ownership
RUN mkdir -p \
        storage/logs \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# ── Config files ──────────────────────────────────────────────────────────────
COPY docker/php/php.ini        /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php/php-fpm.conf   /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
# Fix potential Windows CRLF line-endings and make executable
RUN sed -i 's/\r//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

# Port 8000 = HTTP (Nginx), Port 8080 = WebSocket (Reverb)
EXPOSE 8000 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
