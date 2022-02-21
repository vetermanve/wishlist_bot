#
#--------------------------------------------------------------------------
# Image Setup
# $REF: $
#--------------------------------------------------------------------------
#
# Arguments:
#     PUID                 Индетификатор пользователя. Default "1000"
#     PHP_INSTALL_XDEBUG   Включить расшимрения xdebug в конфиг php (false|true). Default "false"
#     PHP_XDEBUG_PORT      Порт для xdebug. Default "9000"
#

FROM php:7.4-cli-alpine

#
#--------------------------------------------------------------------------
# Core Software's Installation
#--------------------------------------------------------------------------
#

RUN apk --no-cache --update add       \
                    bash              \
                    shadow            \
                    libxml2-dev       \
                    g++               \
                    make              \
                    curl-dev          \
                    libzip-dev        \
                    icu-dev           \
                    postgresql-dev    \
                    autoconf          \
                    rabbitmq-c-dev    \
                    libmemcached-dev  \
                    runit             \
        && rm -rf /tmp/*              \
        && rm -rf /var/cache/apk/*
# END Core Software's Installation  -------------

#
#--------------------------------------------------------------------------
# PHP Installation
#--------------------------------------------------------------------------
#
RUN echo "Installing extensions"        && \
    pecl update-channels                && \
  docker-php-ext-configure zip          && \
  docker-php-ext-configure intl

RUN docker-php-ext-install -j6 \
    zip           \
    intl          \
    bcmath        \
    opcache       \
    pcntl         \
    pgsql         \
    pdo_pgsql     \
    sockets


RUN pecl install -o -f amqp             && \
        docker-php-ext-enable amqp      && \
    pecl install -o -f memcached        && \
        docker-php-ext-enable memcached && \
    pecl install -o -f redis            && \
        docker-php-ext-enable redis     && \
#   Cleaning up
    rm -rf /tmp/* /var/tmp/*
# END PHP Installation --------------------------


#
#--------------------------------------------------------------------------
# PHP Configuration AND COMPOSER
#--------------------------------------------------------------------------
#

ADD .docker/.php-config/50-custom-production.ini        /usr/local/etc/php/conf.d/

# Installing Composer
RUN printf "\nInstalling Composer\n\n"; \
    EXPECTED_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig); \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
    ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');"); \
    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; \
    then \
      >&2 echo 'ERROR: Invalid installer signature'; \
      exit 1; \
    else \
      php composer-setup.php --install-dir=/usr/local/bin --filename=composer; \
    fi; \
    rm composer-setup.php

ENV COMPOSER_ALLOW_SUPERUSER 1

#
#--------------------------------------------------------------------------
# User www-data Configuration
#--------------------------------------------------------------------------
#
RUN cp -r /root/.composer /home/www-data/ \
    && chown -R www-data:www-data /home/www-data \
    && usermod -d /home/www-data www-data

ARG PUID=1000
ENV PUID ${PUID}
RUN if [ -n "${PUID}" ] && [ "${PUID%:*}" != 0 ]; then \
  usermod -u ${PUID} www-data >/dev/null 2>&1 \
;fi


ENV PATH /var/www:$PATH

WORKDIR /var/www

ADD . /var/www

RUN composer install -a --no-progress

RUN rm -f /var/www/.env.local

CMD ["runsvdir", "-P", "/var/www/services/active"]
