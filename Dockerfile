# ===== Stage 1: Node builder =====
FROM node:18 AS nodebuilder
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# ===== Stage 2: Composer builder =====
FROM composer:2 AS composerbuilder
WORKDIR /app

COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ===== Stage 3: PHP + Nginx runtime =====
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl nginx supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy project files
COPY . .

# Copy vendor files from composer stage
COPY --from=composerbuilder /app/vendor ./vendor

# Copy compiled assets from nodebuilder
COPY --from=nodebuilder /app/public/build ./public/build

# Set permissions
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs \
    && chmod -R 777 storage bootstrap/cache

# Cache configs
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && php artisan event:cache

# Copy nginx and supervisor configs
COPY ./nginx.conf /etc/nginx/sites-enabled/default
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080
CMD ["/usr/bin/supervisord"]
