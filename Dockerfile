# Dockerfile for phpLiteCore Development Environment
# Based on PHP 8.3 with Apache

FROM php:8.3-apache

LABEL maintainer="phpLiteCore Team"
LABEL description="Development environment for phpLiteCore PHP Framework"

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    mariadb-client \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by phpLiteCore
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    --with-xpm \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure Apache
# Enable mod_rewrite for clean URLs
RUN a2enmod rewrite

# Update Apache configuration to point to public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure Apache to allow .htaccess
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/phplitecore.conf \
    && a2enconf phplitecore

# PHP Configuration for development
RUN echo 'display_errors = On' >> /usr/local/etc/php/conf.d/custom.ini \
    && echo 'error_reporting = E_ALL' >> /usr/local/etc/php/conf.d/custom.ini \
    && echo 'upload_max_filesize = 64M' >> /usr/local/etc/php/conf.d/custom.ini \
    && echo 'post_max_size = 64M' >> /usr/local/etc/php/conf.d/custom.ini \
    && echo 'memory_limit = 256M' >> /usr/local/etc/php/conf.d/custom.ini \
    && echo 'max_execution_time = 300' >> /usr/local/etc/php/conf.d/custom.ini \
    && echo 'date.timezone = UTC' >> /usr/local/etc/php/conf.d/custom.ini

# OPcache configuration for development
RUN echo 'opcache.enable=1' >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.revalidate_freq=0' >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.validate_timestamps=1' >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.max_accelerated_files=10000' >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.memory_consumption=192' >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.interned_strings_buffer=16' >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.fast_shutdown=1' >> /usr/local/etc/php/conf.d/opcache.ini

# Create storage directories and set permissions
RUN mkdir -p storage/logs storage/cache \
    && chmod -R 777 storage

# Set proper permissions for web server
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
