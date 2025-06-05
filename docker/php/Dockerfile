FROM php:8.3-fpm


RUN apt-get update && apt-get install -y \
      apt-utils \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      libicu-dev \
      zip unzip \
      cron \
      nano \
      git \
      supervisor && \
      docker-php-ext-install pdo_mysql && \
      docker-php-ext-install bcmath && \
      docker-php-ext-install gd && \
      docker-php-ext-install zip && \
      docker-php-ext-install intl && \
      apt-get clean && \
      rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/php.ini

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY /docker/xdebug/xdebug.ini "${PHP_INI_DIR}/conf.d"

WORKDIR /var/www/html

#RUN mkdir -p /root/.ssh && chmod 700 /root/.ssh
#COPY ./docker/ssh/id_rsa /root/.ssh/id_rsa
#RUN chmod 600 /root/.ssh/id_rsa

COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html/storage



