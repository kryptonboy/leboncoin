# syntax=docker/dockerfile:1

# base stage - shared compilation
FROM php:8.5-fpm-alpine AS base

RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    oniguruma-dev

RUN docker-php-ext-install intl mbstring

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./


# dev stage (used in local development, dev deps included)
FROM base AS dev

RUN composer install --no-scripts --no-autoloader --no-interaction

RUN composer dump-autoload --optimize

CMD ["php-fpm"]


# builder stage (used by CI)
FROM base AS builder

RUN composer install --no-scripts --no-autoloader --no-interaction

COPY . .

RUN composer dump-autoload --optimize

CMD ["php", "-v"]


# prod dependencies stage
FROM base AS prop-deps

RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction

COPY . .

RUN composer dump-autoload --optimize --no-dev


# runtime stage (used in production, no dev deps)
FROM php:8.5-fpm-alpine AS runtime

RUN apk add --no-cache icu oniguruma

COPY --from=base /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=base /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

WORKDIR /app

COPY --from=prop-deps /app /app

CMD ["php-fpm"]
