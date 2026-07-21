FROM php:8.4-fpm-alpine

ARG UID=501
ARG GID=20

RUN apk add --no-cache \
        git curl zip unzip icu-dev libzip-dev oniguruma-dev \
        postgresql-dev linux-headers \
        freetype-dev libjpeg-turbo-dev libpng-dev \
        supervisor \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql pgsql intl zip gd bcmath opcache pcntl sockets exif \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY php.ini /usr/local/etc/php/conf.d/zeno.ini

# مستخدم يطابق الهوست حتى لا تُملك الملفات لـ root على الـ bind mount
RUN addgroup -g ${GID} -S app 2>/dev/null || true \
 && adduser  -u ${UID} -S app -G "$(getent group ${GID} | cut -d: -f1)" 2>/dev/null || true

WORKDIR /var/www
USER app

EXPOSE 9000
CMD ["php-fpm"]
