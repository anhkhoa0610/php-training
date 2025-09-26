FROM php:8.1-apache

# Cài mysqli và pdo_mysql để kết nối MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Cài đặt extension Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# Copy php.ini custom vào container
COPY php.ini /usr/local/etc/php/conf.d/php.ini
