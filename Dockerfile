# ==========================================
# Stage 1: Install PHP vendor dependencies
# ==========================================
FROM composer:2.7 AS vendor
WORKDIR /app

# Copy dependency definition files
COPY composer.json composer.lock ./

# Install vendor dependencies (no development dependencies for production)
RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist

# ==========================================
# Stage 2: Compile frontend assets (JS/CSS)
# ==========================================
FROM node:20-alpine AS assets
WORKDIR /app

# Copy node packaging definitions and lockfiles
COPY package.json package-lock.json ./
RUN npm ci

# Copy necessary assets and configuration files
COPY resources/ ./resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./

# Compile public assets
RUN npm run build

# ==========================================
# Stage 3: Runtime environment (Nginx + PHP)
# ==========================================
FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    tzdata

# Install helper script to easily install PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

# Install necessary PHP extensions for Laravel and Maatwebsite Excel
RUN install-php-extensions \
    pdo_mysql \
    zip \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache \
    intl

# Set PHP configurations for production
RUN echo "upload_max_filesize = 64M" > /usr/local/etc/php/conf.d/custom-limits.ini && \
    echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/custom-limits.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom-limits.ini

# Set Opcache production configuration
RUN echo "opcache.enable_cli=1" > /usr/local/etc/php/conf.d/opcache-production.ini && \
    echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache-production.ini && \
    echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache-production.ini && \
    echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache-production.ini && \
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache-production.ini

# Copy application files (with proper ownership for security)
COPY --chown=www-data:www-data . .

# Copy vendors from vendor stage
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor

# Copy compiled assets from assets stage
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

# Copy server configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Prepare directories for Nginx/Supervisor and set permissions
RUN mkdir -p /var/log/supervisor /var/run/nginx /var/log/nginx && \
    touch /var/run/nginx.pid && \
    chown -R www-data:www-data /var/run/nginx.pid /var/cache/nginx /var/log/nginx /var/run/nginx

# Copy and set execution permissions for the entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port (Nginx default setup, entrypoint will adjust to $PORT at runtime)
EXPOSE 80

# Define entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
