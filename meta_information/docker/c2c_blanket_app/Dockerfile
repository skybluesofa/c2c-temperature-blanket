FROM php:8.2-fpm-alpine

# XDebug environmental vars
ENV PHP_XDEBUG_ENABLED=1 \
    PHP_XDEBUG_REMOTE_HOST=host.docker.internal \
    PHP_XDEBUG_REMOTE_PORT=9000

# Install Xdebug
RUN apk add --no-cache --update linux-headers \
    $PHPIZE_DEPS \
    curl \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install \
    zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN apk add icu-dev 
#RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-configure intl && docker-php-ext-install intl

RUN apk update && \
    apk add bash build-base gcc wget git autoconf libmcrypt-dev libzip-dev zip \
    g++ make openssl-dev \
    php81-openssl \
    #    php81-pdo_mysql \
    php81-mbstring

# Copy xdebug config file
COPY meta_information/server_config/c2c_blanket_app/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Expose port 9000 for PHP-FPM
EXPOSE 9003

