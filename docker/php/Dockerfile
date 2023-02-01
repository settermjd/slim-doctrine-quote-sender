ARG ALPINE_VERSION=3.17
ARG PHP_VERSION=8.2

FROM php:${PHP_VERSION}-fpm-alpine${ALPINE_VERSION} as base
COPY --from=composer:2.2.7 /usr/bin/composer /usr/bin/
WORKDIR /var/www/html
RUN apk --update-cache add \
      autoconf \
      gcc \
      linux-headers \
      musl-dev \
      make \
      postgresql-dev \
    && pecl install xdebug \
    && docker-php-ext-install pdo pdo_pgsql \
    && docker-php-ext-enable xdebug;