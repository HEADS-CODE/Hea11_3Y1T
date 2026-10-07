FROM php:8.2-apache

# CodeIgniter requires intl and mbstring; the application uses MySQL.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev \
    && docker-php-ext-install intl mbstring mysqli pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

COPY . .
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

RUN mkdir -p writable/cache writable/logs writable/session writable/debugbar \
    && chown -R www-data:www-data writable

EXPOSE 80
