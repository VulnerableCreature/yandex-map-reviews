FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
        pdo_mysql \
        zip \
        mbstring \
        bcmath

RUN pecl channel-update pecl.php.net \
    && pecl install redis-6.3.0 \
    && docker-php-ext-enable redis

RUN pecl install xdebug

RUN docker-php-ext-enable xdebug

RUN printf '%s\n' \
        'xdebug.mode=debug,develop' \
        'xdebug.start_with_request=yes' \
        'xdebug.client_host=host.docker.internal' \
        'xdebug.client_port=9003' \
        'xdebug.idekey=PHPSTORM' \
        'xdebug.log=/tmp/xdebug.log' \
        'xdebug.log_level=7' \
        > /usr/local/etc/php/conf.d/docker-xdebug.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
