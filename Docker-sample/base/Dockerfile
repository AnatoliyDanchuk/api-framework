FROM alpine:3.16
ARG PHP_VERSION=8.1.10

ARG APP_ENV
ARG FOR_RUN_TESTS

ENV APP_ENV=$APP_ENV

#for command:apk add
RUN apk -U upgrade

# for download php source
RUN apk add --no-cache curl

# for build php with both flags: fpm and zts
RUN curl https://www.php.net/distributions/php-$PHP_VERSION.tar.gz | tar -xz

RUN apk add --no-cache \
    # solve No package 'libcurl' found for success oauth install
    curl-dev \
    # try for --with-openssl
    libressl-dev \
    # for intl
    # because: error: Package requirements (icu-uc >= 50.1 icu-io icu-i18n) were not met
    icu-dev \
    # For pecl install oauth
    # Because error: Couldn't find pcre.h, try installing the libpcre development/headers package \
    pcre-dev \
    # for mbstring because No package 'oniguruma' found
    oniguruma-dev \
    # because configure: error: rtnetlink.h is required, install the linux-headers package: apk add --update linux-headers
    linux-headers

# for build php via ./configure
RUN apk add --no-cache autoconf dpkg-dev dpkg file g++ gcc libc-dev make pkgconf re2c libxml2-dev sqlite-dev

RUN cd php-$PHP_VERSION \
    && ./buildconf --force \
    && ./configure \
        --with-config-file-path=/usr/local/etc/php \
        --with-config-file-scan-dir=/usr/local/etc/php/conf.d \
        --bindir=/usr/local/bin \
        --libdir=/usr/local/lib/php \
        --enable-fastcgi \
        --enable-fpm \
        # need for --enable-fastcgi
        --disable-cgi \
        --with-openssl \
        --with-pear \
        --with-curl \
        --enable-mbstring \
        --enable-intl \
        --enable-oauth \
    && make -j$(nproc) \
    && make install

RUN mkdir -p /usr/local/etc/php/conf.d

# For auth of rest to magento.
RUN pecl install oauth && echo "extension=oauth.so" >> /usr/local/etc/php/conf.d/extensions.ini

# For developers
RUN pecl install xdebug && echo "zend_extension=xdebug.so" >> /usr/local/etc/php/conf.d/extensions.ini

# for work with images
RUN apk add --no-cache imagemagick imagemagick-dev && pecl install imagick && echo "extension=imagick.so" >> /usr/local/etc/php/conf.d/extensions.ini

# for command: composer install
RUN apk add --no-cache unzip

# Install composer
RUN curl https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer
# Avoid to create dir ".composer" in workdir after "composer install"
ENV COMPOSER_CACHE_DIR=/tmp
ENV COMPOSER_HOME=/tmp

WORKDIR /var/www

EXPOSE 9000
CMD ["php-fpm", "--nodaemonize"]

# [Section with application files]

RUN adduser -S www-data -G www-data
# Solve permission conflicts on Ubuntu
RUN apk add --no-cache shadow
RUN groupmod -g 1000 www-data && usermod -u 1000 -g 1000 www-data

COPY --chown=www-data:www-data ./Domain /var/www/Domain
COPY --chown=www-data:www-data ./DomainAdapter /var/www/DomainAdapter
COPY --chown=www-data:www-data ./Api /var/www/Api
COPY --chown=www-data:www-data ./Framework /var/www/Framework

RUN mkdir /var/www/var \
    && chown www-data:www-data /var/www/var

COPY --chown=www-data:www-data ./Docker/base/php.ini /usr/local/etc/php/conf.d/common.ini
RUN rm /usr/local/etc/php-fpm.conf.default
COPY --chown=www-data:www-data ./Docker/base/php-fpm.conf /usr/local/etc/php-fpm.conf

RUN touch /usr/local/var/log/php-fpm.log && \
    chown www-data:www-data /usr/local/var/log/php-fpm.log

# After operations which requires sudo
# and before running of scripts which use application's files
# to avoid issue with access and permissions.
USER www-data:www-data

COPY --chown=www-data:www-data ./Docker/base/composer_install.sh /tmp/composer_install.sh
# Run composer install only for not dev, because dev will run it after mount volume with local code.
RUN if [ "$APP_ENV" = "prod" ] && [ $FOR_RUN_TESTS ]; then \
        bash /tmp/composer_install.sh \
    ;fi
RUN if [ "$APP_ENV" = "prod" ] && ! [ $FOR_RUN_TESTS ]; then \
        bash /tmp/composer_install.sh release \
    ;fi
RUN rm /tmp/composer_install.sh
