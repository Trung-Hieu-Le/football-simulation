FROM php:8.2-fpm

# Use Debian-based image to make extension builds more predictable
WORKDIR /var/www/html

# Install minimal system dependencies required for Laravel and PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       git \
       curl \
       unzip \
       libzip-dev \
       libpng-dev \
       libjpeg-dev \
       libfreetype6-dev \
       zlib1g-dev \
       libonig-dev \
       pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
       pdo_mysql \
       gd \
       zip \
       bcmath \
       opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Provide Composer binary (no global composer install during image build to keep builds fast when using bind mounts)
COPY --from=composer:2.6 /usr/bin/composer /usr/local/bin/composer

# Create common directories and set permissions for www-data
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
