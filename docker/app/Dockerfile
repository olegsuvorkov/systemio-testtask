FROM php:8.3-fpm-alpine3.19

RUN set -eux; \
    apk add --no-cache \
      patch \
      libpq \
      libzip \
      icu \
      icu-data-full \
      oniguruma \
 && apk add --no-cache --virtual .dev \
      libpq-dev \
      libzip-dev \
      icu-dev \
      oniguruma-dev \
 && docker-php-ext-install \
      pdo \
      pdo_pgsql \
      opcache \
      zip \
      intl \
      mbstring \
      sysvsem \
 && apk del .dev

RUN apk add --update --no-cache --virtual .build-dependencies ${PHPIZE_DEPS} linux-headers \
 && yes 'no' | pecl install apcu \
 && pecl install xdebug \
 && docker-php-ext-enable apcu xdebug \
 && pecl clear-cache \
 && apk del .build-dependencies

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN chmod a+rwx /usr/bin/composer

RUN rm -rf /var/www/*

WORKDIR /var/www

VOLUME /var/www
