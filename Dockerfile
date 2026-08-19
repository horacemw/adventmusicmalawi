# syntax=docker/dockerfile:1.7

# ---------- Stage 1: Frontend build ----------
FROM node:24-bookworm-slim AS frontend
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY resources ./resources
COPY vite.config.js tsconfig.json tailwind.config.js postcss.config.js ./
COPY public ./public
COPY routes ./routes
RUN npm run build

# ---------- Stage 2: Composer install ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --no-progress \
    --optimize-autoloader

# ---------- Stage 3: PHP-FPM runtime ----------
FROM php:8.4-fpm-bookworm AS app

# System deps + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git curl unzip supervisor cron \
        libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
        libzip-dev libonig-dev libicu-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP config tuned for a small-to-medium production instance
COPY deployment/php/php.ini /usr/local/etc/php/conf.d/zzz-app.ini
COPY deployment/php/opcache.ini /usr/local/etc/php/conf.d/zzz-opcache.ini
COPY deployment/php/php-fpm.conf /usr/local/etc/php-fpm.d/zzz-app.conf

# Copy composer for later CLI use (migrations etc.)
COPY --from=vendor /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy the application, then vendor + built frontend
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /build/public/build ./public/build

# Ensure Laravel storage dirs exist and are writable by the php-fpm user (www-data)
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/app/public bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Entrypoint runs migrations, caches config/routes/views, then hands off to php-fpm
COPY deployment/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
