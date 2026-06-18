FROM composer:2 AS composer_deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM node:22-alpine AS frontend_builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js vite.frontend.config.js ./
RUN npm run build

FROM php:8.4-cli-alpine
WORKDIR /app

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libpq-dev \
    oniguruma-dev \
    icu-dev \
    libzip-dev \
    nodejs \
    npm \
 && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    bcmath \
    intl \
    zip

COPY --from=composer_deps /app/vendor ./vendor
COPY . .
COPY --from=frontend_builder /app/public/build ./public/build

RUN addgroup -g 1000 www && adduser -D -u 1000 -G www www \
 && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
 && chown -R www:www storage bootstrap/cache public

USER www
EXPOSE 10000
CMD ["sh", "-lc", "\
    UPLOADS=${PERSISTENT_STORAGE_PATH:-/var/data/storage}/app/public && \
    mkdir -p ${UPLOADS}/albums ${UPLOADS}/servicios && \
    if [ -d public/storage ] && [ ! -L public/storage ]; then rm -rf public/storage; fi && \
    if [ -L public/storage ]; then rm public/storage; fi && \
    ln -s ${UPLOADS} public/storage && \
    echo [boot] public/storage -> $(readlink public/storage) && \
    php artisan config:clear && \
    php artisan config:cache && \
    php artisan view:cache && \
    php artisan migrate --force && \
    if [ \"${RUN_SEEDERS:-false}\" = \"true\" ]; then php artisan db:seed --force; fi && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
