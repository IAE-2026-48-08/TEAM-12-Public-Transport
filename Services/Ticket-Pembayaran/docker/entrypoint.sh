#!/bin/sh

# Set storage and bootstrap cache permissions
chmod -R 775 /var/www/storage /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy .env if not exists
if [ ! -f "/var/www/.env" ]; then
    echo "Creating .env file..."
    cp /var/www/.env.example /var/www/.env
    php artisan key:generate
fi

# Install Composer dependencies if vendor folder doesn't exist
if [ ! -d "/var/www/vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

# Wait for MySQL database connection
echo "Waiting for database connection..."
until php -r "
try {
    \$dbh = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    exit(0);
} catch (PDOException \$e) {
    exit(1);
}
"; do
    echo "Database not ready yet, retrying..."
    sleep 2
done
echo "Database is ready!"

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Execute container's main command (php-fpm)
exec "$@"
