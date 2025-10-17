# Stage 1: Build Dependencies
# Use a PHP-FPM Alpine base image
FROM php:8.3-fpm-alpine AS build

# Set the build environment variables
ENV APP_ENV=production

# Install system dependencies and PHP extensions
# Added 'oniguruma-dev' to fix the mbstring compilation error.
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

# Copy ALL application files necessary for Composer
# This ensures 'artisan' is present for the post-autoload-dump script.
COPY composer.json composer.lock ./
COPY . .

# Install PHP dependencies
# This step will now succeed because 'artisan' is in the current directory.
RUN composer install --no-dev --optimize-autoloader

# Optional: Run initial Artisan commands (e.g., key generation)
# RUN php artisan key:generate

# Set proper permissions for the application (important for Laravel/Lumen)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# --------------------------------------------------------------------------

# Stage 2: Final Runtime Image
# Start fresh with a lean runtime image
FROM php:8.3-fpm-alpine

# Install ONLY the necessary runtime dependencies.
# Still need 'oniguruma-dev' here for runtime libraries if not using a multi-stage copy for vendor.
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    oniguruma-dev \
    # Install PHP extensions for the final image
    && docker-php-ext-install pdo_pgsql pdo mbstring zip opcache

# Set the application working directory
WORKDIR /var/www/html

# Copy built application files (including vendor/ directory) from the 'build' stage
COPY --from=build /var/www/html .

# Re-apply permissions if necessary
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose the port (e.g., 10000 for Render)
EXPOSE 10000

# ----------------------------------------------------------------------------
# Literate note: Optional, safe doc import for production
# ----------------------------------------------------------------------------
# We sometimes want fresh production environments to include baseline docs.
# To avoid accidental overwrites, our DocsSeeder is production-safe:
# - In production it only creates missing docs (never updates existing ones).
# - It reads from resources/docs by default, or DOCS_FS_IMPORT_PATH if set.
# Enable this once by setting SEED_DOCS_ON_BOOT=true in the environment for a deploy.
# Leave it unset/false for normal runs to avoid repeated work on every start.
ENV SEED_DOCS_ON_BOOT=false
ENV RESET_ADMIN_PASSWORD_ON_BOOT=false

# Define the command to start your application (Laravel/Lumen)
# On boot: run migrations, optionally reset admin password, optionally seed docs, then start the HTTP server.
CMD ["sh", "-c", "php artisan migrate --force \
    && php artisan db:seed --force \
    && if [ \"$RESET_ADMIN_PASSWORD_ON_BOOT\" = \"true\" ]; then php artisan db:seed --class='Database\\Seeders\\AdminPasswordResetSeeder' --force; fi \
    && if [ \"$SEED_DOCS_ON_BOOT\" = \"true\" ]; then php artisan db:seed --class='Database\\Seeders\\DocsSeeder' --force; fi \
    && php artisan serve --host=0.0.0.0 --port=10000"]