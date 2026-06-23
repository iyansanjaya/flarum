#!/bin/sh
set -e

# Auto-install Flarum if not present (first run)
if [ ! -f /var/www/flarum/composer.json ]; then
    echo "=========================================="
    echo " Flarum not detected. Installing..."
    echo "=========================================="
    # Install as www-data for correct permissions
    su-exec www-data composer create-project flarum/flarum /var/www/flarum --stability=stable --no-interaction
    echo "=========================================="
    echo " Flarum installed successfully!"
    echo " Open your URL to run the web installer."
    echo "=========================================="
else
    echo "Flarum already installed. Skipping installation..."
fi

# Execute the main command (php-fpm)
exec "$@"
