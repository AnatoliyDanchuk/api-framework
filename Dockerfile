FROM php:8-cli
RUN apt-get update
RUN apt-get install -y unzip git
RUN docker-php-ext-configure intl \
  && docker-php-ext-install intl \
  && docker-php-ext-install oauth
RUN pecl install xdebug \
  && docker-php-ext-enable xdebug
RUN mkdir /app
RUN chmod -R 777 /app
WORKDIR /app
COPY --from=composer /usr/bin/composer /usr/bin/composer