# ===== Stage A: Composer dependencies =====
FROM php:8.2-cli AS composerbuilder

# Install system packages and intl extension early
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpng-dev libonig-dev libxml2-dev zip curl \
 && docker-php-ext-install intl pdo_mysql mbstring exif pcntl bcmath gd \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy full project (for artisan discover)
COPY . .

RUN php artisan package:discover || true


# ===== Stage B: Node frontend build =====
FROM node:18 AS nodebuilder
WORKDIR /app

COPY package*.json ./
RUN npm ci --legacy-peer-deps
COPY . .
COPY --from=composerbuilder /app/vendor ./vendor
RUN npm run build


# ===== Stage C: Runtime (PHP-FPM + Nginx) =====
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpng-dev libonig-dev libxml2-dev zip curl nginx supervisor \
 && docker-php-ext-install intl pdo_mysql mbstring exif pcntl bcmath gd \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .
COPY --from=composerbuilder /app/vendor ./vendor
COPY --from=nodebuilder /app/public/build ./public/build

RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs \
 && chmod -R 777 storage bootstrap/cache \
 && php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache || true

COPY ./nginx.conf /etc/nginx/sites-enabled/default
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord","-n"]
