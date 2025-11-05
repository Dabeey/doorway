# ===== Stage 1: Node builder =====
FROM node:18 AS nodebuilder
WORKDIR /app

# Copy and install frontend dependencies
COPY package*.json ./
RUN npm ci

# Copy all project files (excluding ignored files)
COPY . .

# ===== Stage 2: Composer dependencies =====
FROM composer:2 AS composerbuilder
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ===== Stage 3: PHP + Nginx final image =====
FROM php:8.2-fpm

# Install required system packages
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl nginx supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy all files from your project
COPY . .

# Copy vendor folder from composer stage
COPY --from=composerbuilder /app/vendor ./vendor

# Copy node_modules and build assets from nodebuilder
COPY --from=nodebuilder /app/node_modules ./node_modules
RUN npm run build

# Set correct folder permissions
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs \
    && chmod -R 777 storage bootstrap/cache

# Cache Laravel configs
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && php artisan event:cache

# Copy Nginx + Supervisor configs
COPY ./nginx.conf /etc/nginx/sites-enabled/default
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord"]
