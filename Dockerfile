FROM richarvey/nginx-php-fpm:3.1.6

COPY . .

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