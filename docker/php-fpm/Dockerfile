FROM php:8.3-fpm-alpine

# Build arguments
ARG PHP_VERSION=8.3

# Install system dependencies and build tools
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    postgresql-dev \
    mysql-dev \
    oniguruma-dev \
    libxml2-dev \
    rabbitmq-c-dev \
    linux-headers \
    autoconf \
    g++ \
    gcc \
    make \
    supervisor \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mysqli \
        pdo_mysql \
        zip \
        gd \
        intl \
        mbstring \
        xml \
        bcmath \
        opcache \
        sockets \
        pcntl

# Install Redis extension
RUN pecl install redis-6.0.2 \
    && docker-php-ext-enable redis

# Install AMQP extension for RabbitMQ
RUN pecl install amqp-2.1.2 \
    && docker-php-ext-enable amqp

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Create necessary directories
RUN mkdir -p /var/log/php-fpm \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /run/php

# Copy configuration files
COPY php.ini /usr/local/etc/php/php.ini
COPY www.conf /usr/local/etc/php-fpm.d/www.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create php-fpm user
RUN addgroup -g 1000 www \
    && adduser -D -u 1000 -G www www \
    && chown -R www:www /var/www \
    && chown -R www:www /var/log/php-fpm \
    && chown -R www:www /var/log/supervisor

# Expose ports
EXPOSE 9000 9001

# Health check script
RUN echo '#!/bin/sh' > /healthcheck.sh \
    && echo 'php-fpm -t 2>&1 | grep -q "successful" && exit 0 || exit 1' >> /healthcheck.sh \
    && chmod +x /healthcheck.sh

# Start PHP-FPM directly (without supervisor for now)
CMD ["php-fpm", "-F", "-R"]
