FROM php:8.3-fpm-alpine

# Install system dependencies required by Flarum
RUN apk add --no-cache \
    curl git unzip su-exec libzip-dev libpng-dev oniguruma-dev icu-dev

# Install PHP extensions required by Flarum
RUN docker-php-ext-install pdo_mysql mbstring zip gd exif intl

# Custom PHP config (disable display_errors for clean JSON API responses)
# COPY php.ini /usr/local/etc/php/conf.d/99-flarum.ini

# Get Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/flarum

# Set permissions (www-data is the default PHP/Nginx user)
RUN chown -R www-data:www-data /var/www/flarum

# Entrypoint script: auto-install Flarum if not present
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]