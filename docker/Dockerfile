FROM php:8.2-fpm-alpine

# Install dev dependencies
RUN apk update \
    && apk upgrade --available \
    && apk add --virtual build-deps \
        autoconf \
        build-base \
        icu-dev \
        libevent-dev \
        openssl-dev \
        zlib-dev \
        libzip \
        libzip-dev \
        zlib \
        zlib-dev \
        bzip2 \
        git \
        libpng \
        libpng-dev \
        libjpeg \
        libjpeg-turbo-dev \
        libwebp-dev \
        freetype \
        freetype-dev \
        postgresql-dev \
        curl \
        wget \
        bash \
        rabbitmq-c \
        rabbitmq-c-dev \
        linux-headers \
        librdkafka-dev

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) \
    intl \
    gd \
    bcmath \
    pdo_pgsql \
    pcntl \
    sockets \
    zip
RUN pecl channel-update pecl.php.net \
    && pecl install -o -f \
        rdkafka \
        event \
    && rm -rf /tmp/pear \
    && echo "extension=rdkafka.so" > /usr/local/etc/php/conf.d/rdkafka.ini \
    && echo "extension=event.so" > /usr/local/etc/php/conf.d/event.ini
