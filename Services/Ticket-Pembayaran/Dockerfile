FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libjpeg-dev \
    libfreetype6-dev \
    default-mysql-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure and install GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Adjust permissions for storage and bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Fix line endings of entrypoint script and make it executable
RUN sed -i 's/\r$//' /var/www/docker/entrypoint.sh && chmod +x /var/www/docker/entrypoint.sh

# Set entrypoint
ENTRYPOINT ["/var/www/docker/entrypoint.sh"]

EXPOSE 9000
CMD ["php-fpm"]
