# Stage 1: Build Dependencies
# Use a PHP-FPM Alpine base image
FROM php:8.3-fpm-alpine AS build

# Set the build environment variables if necessary
ENV APP_ENV=production

# Install system dependencies and PHP extensions
# The 'oniguruma-dev' package is required to compile the 'mbstring' extension.
# It is added to fix the "Package 'oniguruma' not found" error.
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    # Install PHP extensions
    && docker-php-ext-install pdo_pgsql pdo mbstring zip opcache

# Set working directory
WORKDIR /var/www/html

# Copy Composer binary from its dedicated image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy the rest of the application files
COPY . .

# Optional: Run Artisan commands
# If you are using Laravel/Lumen, these steps are common.
# Note: Key generation is often better handled by a Render Environment Variable.
# RUN php artisan key:generate
# RUN php artisan cache:clear

# Set proper permissions for the application (important for Laravel/Lumen)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# --------------------------------------------------------------------------

# Stage 2: Final Runtime Image
# Start fresh with a lean runtime image
FROM php:8.3-fpm-alpine

# Install ONLY the necessary runtime dependencies (no 'git' or 'unzip')
# Still need 'oniguruma-dev' here if you re-install extensions, 
# or if you rely on system packages for runtime.
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    oniguruma-dev \
    # Install PHP extensions for the final image
    && docker-php-ext-install pdo_pgsql pdo mbstring zip opcache

# Set the application working directory
WORKDIR /var/www/html

# Copy built application from the 'build' stage
COPY --from=build /var/www/html .

# Re-apply permissions if necessary (Good practice)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose the standard port (Render typically uses 10000)
EXPOSE 10000

# Define the command to start your application
# For a PHP-FPM web service, you need to run the web server (e.g., Nginx/Caddy) 
# and PHP-FPM. Since this image is just PHP-FPM, you may need a separate 
# web server service or use a different base image (e.g., caddy/php-fpm).
#
# If this is a Laravel/Lumen API/Service, you might run the built-in server for simplicity:
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]

# Alternatively, if you are running PHP-FPM, you would typically use:
# CMD ["php-fpm"]