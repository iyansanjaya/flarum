#!/bin/sh
set -e

# Fix ownership on mounted volume (runs as root)
chown -R www-data:www-data /var/www/flarum

# Auto-install Flarum if not present (first run)
if [ ! -f /var/www/flarum/composer.json ]; then
    echo "=========================================="
    echo " Flarum not detected. Installing..."
    echo "=========================================="
    # Install as www-data for correct permissions
    su-exec www-data composer create-project flarum/flarum /var/www/flarum --no-interaction

    # Ensure beta extensions (e.g. FoF) can be installed, while preferring stable
    su-exec www-data php -r '$f="/var/www/flarum/composer.json"; $j=json_decode(file_get_contents($f),true); $j["minimum-stability"]="beta"; $j["prefer-stable"]=true; file_put_contents($f,json_encode($j,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));'

    echo "=========================================="
    echo " Flarum installed successfully!"
    echo " Open your URL to run the web installer."
    echo "=========================================="
else
    echo "Flarum already installed. Skipping installation..."
fi

# Execute the main command (php-fpm)
exec "$@"
