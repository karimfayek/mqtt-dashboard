FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker rm $(docker ps -a -q) 2>/dev/null || true

# PHP Extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps
COPY . /var/www/html
RUN composer install

# Set permissions (لـ storage و bootstrap)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
