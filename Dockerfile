# Stage 1: Build with PHP and Composer
FROM php:8.2-fpm as builder

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files and install dependencies to leverage Docker cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-autoloader --no-scripts

# Copy the rest of the application
COPY . .

# Run composer scripts
RUN composer dump-autoload --optimize

# Stage 2: Production Image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install production dependencies including Nginx and Supervisor
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    ffmpeg \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql exif pcntl bcmath gd zip

# Copy application files from the builder stage
COPY --from=builder /var/www .

# Copy Nginx and Supervisor configurations
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set correct permissions for storage and bootstrap cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port 5003 for Nginx
EXPOSE 5003

# Start Supervisor to manage Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]