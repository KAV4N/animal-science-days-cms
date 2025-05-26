# PHP Dockerfile for Laravel
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files with proper ownership
COPY --chown=www-data:www-data . /var/www/html

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Clear any existing cache files that might have wrong permissions
RUN rm -rf /var/www/html/storage/framework/views/*
RUN rm -rf /var/www/html/storage/framework/sessions/*
RUN rm -rf /var/www/html/bootstrap/cache/*

# Create storage directories if they don't exist
RUN mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/app/public \
    /var/www/html/storage/app/private \
    /var/www/html/bootstrap/cache

# Set proper permissions for Laravel directories and files
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    touch /var/www/html/storage/logs/laravel.log && \
    chown www-data:www-data /var/www/html/storage/logs/laravel.log && \
    chmod 664 /var/www/html/storage/logs/laravel.log

# Create storage symbolic link for file uploads
RUN php artisan storage:link || true

# Switch to www-data user
USER www-data

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]