FROM php:8.3-apache

RUN apt-get -y update --fix-missing \
    && apt-get --no-install-recommends install -y apt-utils \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mysqli
RUN a2enmod rewrite headers

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/prod/php-overrides.ini /usr/local/etc/php/conf.d/overrides.ini
COPY docker/prod/apache-overrides.conf /etc/apache2/conf-enabled/overrides.conf
COPY www/ /var/www/html/
