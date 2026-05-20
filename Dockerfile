FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libonig-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring \
    && a2enmod rewrite \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/uploads \
    && chmod -R 775 /var/www/html/storage /var/www/html/uploads

ENV APP_ENV=production
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80