# ---------- STAGE 1: Build frontend assets with Node ----------
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build


# ---------- STAGE 2: Laravel app with PHP + Nginx ----------
FROM richarvey/nginx-php-fpm:3.1.6

COPY . .

# Copy compiled Vite assets from Stage 1
COPY --from=frontend /app/public/build /var/www/html/public/build

# Image config
ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1

# Laravel config
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

# Install composer dependencies
RUN composer install --optimize-autoloader --no-dev

# Allow Nginx to write to storage/cache
RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

CMD ["/start.sh"]