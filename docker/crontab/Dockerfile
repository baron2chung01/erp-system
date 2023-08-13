FROM php:8.0.5-fpm-alpine

# Fix: iconv(): Wrong charset, conversion from UTF-8 to UTF-8//IGNORE is not allowed in Command line code on line 1
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
RUN apk add --no-cache --repository http://dl-3.alpinelinux.org/alpine/edge/community gnu-libiconv

# Install php extensions
RUN apk update \
    && apk add --no-cache libzip-dev libmcrypt libmcrypt-dev zlib-dev php8-pcntl \
    && docker-php-ext-install exif zip bcmath mysqli pdo pdo_mysql ctype pcntl

# Install GD extensions
RUN apk add --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev && \
    docker-php-ext-configure gd \
    --with-freetype=/usr/include/ \
    --with-jpeg=/usr/include/ && \
    NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
    docker-php-ext-install -j${NPROC} gd && \
    apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev

ENV APP_USER appuser

RUN adduser -g "App User" -D $APP_USER

COPY crontab /etc/crontabs/$APP_USER

RUN chmod 0600 /etc/crontabs/$APP_USER

CMD ["crond", "-f", "-d", "8"]
