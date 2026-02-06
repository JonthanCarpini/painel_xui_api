FROM php:8.2-fpm

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
    nginx \
    default-mysql-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install dependencies (no scripts yet)
RUN composer install --no-dev --no-scripts --no-autoloader

# Copy existing application directory contents
COPY . .

# Generate autoloader and run scripts
RUN composer dump-autoload --optimize --no-dev

# Copy Nginx config
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/nginx/default.conf /etc/nginx/sites-enabled/default

# Copy Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Create system user to run Composer and Artisan Commands
RUN chown -R www-data:www-data /var/www

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
