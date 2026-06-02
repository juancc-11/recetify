FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        git zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN sed -ri -e 's!/var/www/html!/var/www/html/web!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN composer install --optimize-autoloader --prefer-dist --no-interaction --no-scripts

COPY . /var/www/html
RUN chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/runtime /var/www/html/web/assets \
    && chown -R www-data:www-data /var/www/html/runtime /var/www/html/web/assets || true

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/root/.composer

EXPOSE 80
