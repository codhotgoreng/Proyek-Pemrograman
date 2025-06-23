# Gunakan image PHP 8.2 dan Composer
FROM composer:2.6 as build

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

FROM php:8.2-cli

WORKDIR /app

COPY --from=build /app /app

RUN apt-get update && apt-get install -y \
    libzip-dev unzip zlib1g-dev libpng-dev \
    && docker-php-ext-install zip pdo pdo_mysql

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
