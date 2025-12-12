# Use the official FrankenPHP image with PHP 8.3 and Debian Bookworm base
FROM dunglas/frankenphp:php8.3

# 1. Install System Dependencies (for database and Git)
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libpq-dev \
        libzip-dev \
        acl \
    && rm -rf /var/lib/apt/lists/*

# 2. Install PHP Extensions (for PostgreSQL)
# The FrankenPHP base image provides a script for easy extension installation.
RUN install-php-extensions pdo_pgsql zip

COPY . /app

# 3. Set up the working directory and user
WORKDIR /app

# 4. Install Composer (needed to install Symfony dependencies)
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# 5. Copy Composer files and install dependencies
# This step leverages Docker cache: dependencies are only re-installed if composer.* changes.
COPY composer.json composer.lock symfony.lock ./
# Install only necessary runtime dependencies for the production-like FrankenPHP environment
RUN set -eux; \
    composer install --prefer-dist --no-progress --no-interaction

# 7. Configure correct permissions (important for cache/logs)
RUN set -eux; \
    install -d -m 0777 -o www-data -g www-data var public/build public/bundles; \
    # Set ACLs to allow www-data (the user FrankenPHP runs as) to write to var/
    setfacl -R -m u:www-data:rwX -m u:$(whoami):rwX var; \
    setfacl -R -m d:u:www-data:rwX -m d:u:$(whoami):rwX var