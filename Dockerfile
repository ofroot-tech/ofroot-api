# Stage 1: Build Dependencies
FROM php:8.3-fpm-alpine AS build

# Install system dependencies and PHP extensions
RUN apk add --no-cache git unzip libzip-dev postgresql-dev \
    && docker-php-ext-install pdo_pgsql pdo mbstring zip opcache

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy the rest of the application
COPY . .

# Optional: generate app key
RUN php artisan key:generate

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Stage 2: Runtime image (can be same as build for simplicity)
FROM php:8.3-fpm-alpine

# Install PHP extensions
RUN apk add --no-cache postgresql-dev libzip-dev \
    && docker-php-ext-install pdo_pgsql pdo mbstring zip opcache

# Set working directory
WORKDIR /var/www/html

# Copy built app from build stage
COPY --from=build /var/www/html .

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port Render expects
EXPOSE 10000

# Start PHP-FPM
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
