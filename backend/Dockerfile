FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip \
    libsqlite3-dev \
    sqlite3 \
    libsodium-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install sodium pdo pdo_sqlite xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application files
COPY . .

# Install dependencies and setup database
RUN composer install --no-interaction --optimize-autoloader --ignore-platform-reqs
RUN php bin/console doctrine:migrations:migrate --no-interaction
RUN php bin/console doctrine:fixtures:load --no-interaction

EXPOSE 8000 8001 8002 8003

# Default command runs the main backend
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
