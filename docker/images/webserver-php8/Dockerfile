FROM php:8.3-apache

# Update
RUN apt-get -y update --fix-missing && \
  apt-get upgrade -y && \
  apt-get --no-install-recommends install -y apt-utils

# Install tools && libraries
RUN apt-get -y install --fix-missing apt-utils nano wget dialog \
  build-essential git curl \
  && rm -rf /var/lib/apt/lists/*

# Other PHP Extensions
RUN docker-php-ext-install mysqli

# Enable apache modules
RUN a2enmod rewrite headers
