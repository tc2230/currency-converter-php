FROM bitnami/laravel:latest

RUN apt-get update && apt-get install nano

RUN mkdir -p /currency-converter-php

COPY ./ /currency-converter-php/

WORKDIR /currency-converter-php/

# RUN composer clearcache
# RUN sudo rm -rf vendor/
# RUN sudo rm composer.lock
RUN composer require predis/predis
RUN composer install
RUN php artisan migrate

# CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]