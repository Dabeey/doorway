# ===== Stage A: Composer (install PHP deps) =====
FROM composer:2 AS composerbuilder
WORKDIR /app
ENV COMPOSER_MEMORY_LIMIT=-1

# Copy full repo before install (so artisan + vendor discovery works)
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist


# ===== Stage B: Node (build frontend) =====
FROM node:20 AS nodebuilder
WORKDIR /app

# Install node deps
COPY package*.json ./
RUN npm ci --legacy-peer-deps

# Copy all source code
COPY . .

# Bring vendor from composer stage (so Vite + Livewire Flux resolve vendor paths)
COPY --from=composerbuilder /app/vendor ./vendor

# Build production assets
RUN npm run build


# ===== Stage C: Runtime (PHP-FPM + Nginx via Supervisor) =====
FROM php:8.2-fpm

# Install system deps and PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev libicu-dev zip curl nginx supervisor \
    && docker-php-ext-install intl pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy project files
COPY . .

# Copy vendor from composer build
COPY --from=composerbuilder /app/vendor ./vendor

# Copy built frontend assets
COPY --from=nodebuilder /app/public/build ./public/build

# Ensure proper storage and cache directories
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs \
    && chmod -R 777 storage bootstrap/cache

# Pre-cache Laravel configs (ignore missing .env issues gracefully)
RUN php artisan key:generate --force || true \
 && php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache || true \
 && php artisan event:cache || true

# Copy Nginx and Supervisor configs (make sure they exist)
COPY ./nginx.conf /etc/nginx/sites-enabled/default
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

# Run supervisord to manage both PHP-FPM and Nginx
CMD ["/usr/bin/supervisord","-n"]
