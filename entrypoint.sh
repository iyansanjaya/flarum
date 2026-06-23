#!/bin/sh
set -e

# Auto-install Flarum jika belum ada (pertama kali run)
if [ ! -f /var/www/flarum/composer.json ]; then
    echo "=========================================="
    echo " Flarum belum terdeteksi. Menginstall..."
    echo "=========================================="
    # Install sebagai www-data agar permission benar
    su-exec www-data composer create-project flarum/flarum /var/www/flarum --stability=stable --no-interaction
    echo "=========================================="
    echo " Flarum berhasil diinstall!"
    echo " Buka URL Anda untuk menjalankan installer web."
    echo "=========================================="
else
    echo "Flarum sudah terinstall. Melewati instalasi..."
fi

# Jalankan command utama (php-fpm)
exec "$@"
