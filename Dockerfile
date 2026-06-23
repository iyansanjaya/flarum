FROM php:8.2-fpm-alpine

# Install dependensi sistem yang dibutuhkan Flarum
RUN apk add --no-cache \
    curl git unzip libzip-dev libpng-dev oniguruma-dev icu-dev

# Install ekstensi PHP yang diwajibkan Flarum
RUN docker-php-ext-install pdo_mysql mbstring zip gd exif intl

# Ambil Composer dari official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur working directory
WORKDIR /var/www/flarum

# Atur permission (www-data adalah user default PHP/Nginx)
RUN chown -R www-data:www-data /var/www/flarum

# Script entrypoint: auto-install Flarum jika belum ada
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]