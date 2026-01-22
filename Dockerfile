FROM php:8.4-cli-alpine AS base

WORKDIR /var/www/html

RUN apk add --no-cache \
    git \
    curl \
    libpng \
    libjpeg-turbo \
    freetype \
    libwebp \
    oniguruma \
    libxml2 \
    libzip \
    icu-libs \
    aria2 \
    bash

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    icu-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        soap \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

############################################
# Dev Image
############################################
FROM base AS development

RUN apk add --no-cache \
    nodejs \
    npm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

############################################
# Builder Image
############################################
FROM development AS builder

ENV COMPOSER_NO_SCRIPTS=1

COPY composer.json composer.lock ./

COPY platform/core ./platform/core
COPY platform/packages ./platform/packages
COPY platform/plugins ./platform/plugins
COPY platform/themes ./platform/themes

COPY artisan ./artisan
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY routes ./routes

RUN mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/views storage/framework/sessions

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts \
    --prefer-dist

############################################
# Final Image
############################################
FROM base

COPY --from=builder /var/www/html/vendor ./vendor

COPY --chown=www-data:www-data . .

RUN chmod -R 755 storage bootstrap/cache

USER www-data
