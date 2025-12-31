FROM php:8.1-fpm-alpine

RUN apk add --no-cache git unzip libpng-dev libzip-dev
RUN docker-php-ext-install pdo_mysql gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN cp .env.example .env || true
RUN php artisan key:generate

EXPOSE 9000
CMD ["php-fpm"]
