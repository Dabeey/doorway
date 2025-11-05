# ===== Stage A: Composer (install PHP dependencies) =====
FROM php:8.2-fpm AS composerbuilder

# Install system packages and PHP extensions required by Laravel + Filament
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev libicu-dev zip curl \
  && docker-php-ext-install intl pdo_mysql mbstring exif pcntl bcmath gd \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer manually (use latest version)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app
ENV COMPOSER_MEMORY_LIMIT=-1

# Copy composer files first (layer caching)
COPY composer.json composer.lock* ./

# Install PHP dependencies without dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy the full source code (after dependencies)
COPY . .



# ===== Stage B: Node (build frontend) =====
FROM node:18 AS nodebuilder
WORKDIR /app

# Copy and install Node dependencies
COPY package*.json ./
RUN npm ci --legacy-peer-deps

# Copy project source
COPY . .

# Bring in vendor folder from composer stage (Vite may need PHP vendor imports)
COPY --from=composerbuilder /app/vendor ./vendor

# Build production assets
RUN npm run build



# ===== Stage C: Runtime (PHP-FPM + Nginx + Supervisor) =====
FROM php:8.2-fpm

# Install system packages and extensions again for runtime
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev libicu-dev zip curl nginx supervisor \
  && docker-php-ext-install intl pdo_mysql mbstring exif pcntl bcmath gd \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy full application source
COPY . .

# Copy vendor dependencies from composer stage
COPY --from=composerbuilder /app/vendor ./vendor

# Copy compiled frontend assets from node stage
COPY --from=nodebuilder /app/public/build ./public/build

# Ensure permissions for Laravel writable folders
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs \
    && chmod -R 777 storage bootstrap/cache

# Laravel cache optimizations (ignore errors if .env not set yet)
RUN php artisan key:generate --force || true \
    && php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true \
    && php artisan event:cache || true

# Copy nginx and supervisor configurations
COPY ./nginx.conf /etc/nginx/sites-enabled/default
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose web port
EXPOSE 8080

# Start Supervisor (manages PHP-FPM + Nginx)
CMD ["/usr/bin/supervisord","-n"]
