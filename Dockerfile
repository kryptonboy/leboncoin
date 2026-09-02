# syntax=docker/dockerfile:1

FROM php:8.5-fpm-alpine

RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    oniguruma-dev

RUN docker-php-ext-install intl mbstring

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-scripts --no-autoloader --no-interaction

COPY . .

RUN composer dump-autoload --optimize

CMD ["php-fpm"]
