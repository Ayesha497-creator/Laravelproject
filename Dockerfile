FROM php:8.1-fpm-alpine

RUN apk add --no-cache git unzip curl libxml2-dev libpng-dev libzip-dev

RUN docker-php-ext-install pdo_mysql xml mbstring zip gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --ignore-platform-reqs
RUN cp .env.example .env
RUN php artisan key:generate

EXPOSE 9000
CMD ["php-fpm"]
