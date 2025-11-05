# ===== Stage 1: Node build =====
FROM node:18 AS nodebuilder

WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ===== Stage 2: PHP + Nginx =====
FROM php:8.2-fpm

# Install system deps and PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl nginx supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www

# Copy project source
COPY . .

# Copy built assets from node stage
COPY --from=nodebuilder /app/public/build /var/www/public/build

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Laravel caches
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache

# Permissions
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs \
    && chmod -R 777 storage bootstrap/cache

# Copy configs
COPY ./nginx.conf /etc/nginx/sites-enabled/default
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080
CMD ["/usr/bin/supervisord"]
