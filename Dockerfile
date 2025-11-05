# ===== Stage 1: Node builder =====
FROM node:18 AS nodebuilder
WORKDIR /app

# Copy package files first
COPY package*.json ./
RUN npm ci

# Copy source code
COPY . .

# --- IMPORTANT ---
# Build will run *after* Composer installation in Stage 2
# so skip build here

# ===== Stage 2: PHP + Nginx =====
FROM php:8.2-fpm

# System deps
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl nginx supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /app

# Copy composer from image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Install PHP dependencies first (creates /vendor)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy Node deps from previous stage and build
COPY --from=nodebuilder /app/node_modules ./node_modules
RUN npm run build

# Cache Laravel config
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && php artisan event:cache

# Copy nginx + supervisor configs if any
# COPY ./nginx.conf /etc/nginx/sites-enabled/default
# COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=8080
