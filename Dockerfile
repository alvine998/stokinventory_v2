# ============================================================
# Stage 1: Build front-end assets with Node
# ============================================================
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ============================================================
# Stage 2: PHP-FPM application image
# ============================================================
FROM php:8.4-fpm-alpine AS app

# Install system-level dependencies
RUN apk add --no-cache \
        bash \
        git \
        curl \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        libzip-dev \
        freetype-dev \
        icu-dev \
        oniguruma-dev \
        fontconfig \
        ttf-freefont \
        zip \
        unzip \
        shadow

# Configure and install PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        pcntl

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Use production PHP settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Tune PHP for Laravel
COPY docker/php/local.ini "$PHP_INI_DIR/conf.d/local.ini"

WORKDIR /var/www

# Install Composer dependencies (cached layer — re-runs only when composer.* changes)
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --optimize-autoloader

# Copy the rest of the application source
COPY . .

# Copy pre-built Vite assets from Stage 1
COPY --from=node-builder /app/public/build ./public/build

# Keep a copy of public/ that won't be shadowed by the shared volume mount
RUN cp -a /var/www/public /var/www/public-src

# Ensure writable runtime directories exist before artisan runs
RUN mkdir -p /var/www/storage/framework/cache \
             /var/www/storage/framework/sessions \
             /var/www/storage/framework/views \
             /var/www/storage/logs \
             /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Finalise Composer autoloader with full source available
RUN composer dump-autoload --optimize --no-dev

# Lock down ownership
RUN chown -R www-data:www-data /var/www

# Entrypoint — syncs public/ into the shared nginx volume on first start
COPY docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
