# ===== Stage A: Composer (install PHP deps) =====
FROM composer:2 AS composerbuilder
WORKDIR /app
# Copy only php deps files first for layer caching
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy full repo (some composer packages may need files)
COPY . .

# ===== Stage B: Node (build frontend) =====
FROM node:18 AS nodebuilder
WORKDIR /app

# Install node deps
COPY package*.json ./
RUN npm ci --legacy-peer-deps

# Copy project files
COPY . .

# Bring vendor from composer stage so vite can resolve vendor imports (livewire flux etc.)
COPY --from=composerbuilder /app/vendor ./vendor

# Run production build
RUN npm run build

# ===== Stage C: Runtime (PHP-FPM + Nginx via Supervisor) =====
FROM php:8.2-fpm

# System packages and PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl nginx supervisor \
  && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy app source
COPY . .

# Copy vendor from composer stage
COPY --from=composerbuilder /app/vendor ./vendor

# Copy the compiled frontend assets from node stage
COPY --from=nodebuilder /app/public/build ./public/build

# Ensure permissions for storage & cache
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs \
    && chmod -R 777 storage bootstrap/cache

# Pre-cache Laravel configuration (ensure APP_KEY exists in env on deploy)
RUN php artisan key:generate --force || true
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true
RUN php artisan event:cache || true

# Copy configs (must exist in repo root)
COPY ./nginx.conf /etc/nginx/sites-enabled/default
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

# Run supervisord in foreground
CMD ["/usr/bin/supervisord","-n"]
