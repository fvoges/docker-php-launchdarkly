FROM composer:latest as vendor
LABEL org.opencontainers.image.authors="fvoges@gmail.com"
LABEL org.opencontainers.image.title="docker-php-launchdarkly"
LABEL org.opencontainers.image.url="https://github.com/fvoges/docker-php-launchdarkly"
LABEL org.opencontainers.image.description="Simple single-page PHP Docker application to demonstrate Launch Darkly"

WORKDIR /app

COPY composer.json composer.json
# COPY composer.lock composer.lock

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist

COPY web/* ./
RUN composer dump-autoload

FROM php:8.3-apache
# RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql
# RUN mkdir /data && chown www-data:www-data /data
# VOLUME /data

# Copy Composer dependencies
COPY --from=vendor app/vendor /var/www/html/vendor/

COPY web/ ./
