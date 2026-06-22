FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        curl \
        git \
        iputils-ping \
        libonig-dev \
        less \
        netcat-traditional \
        pkg-config \
        procps \
        unzip \
        zip \
    && docker-php-ext-install mbstring opcache \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/99-kotupanovklima.ini
COPY docker/entrypoint.sh /usr/local/bin/kotupanovklima-entrypoint

RUN chmod +x /usr/local/bin/kotupanovklima-entrypoint

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/kotupanovklima-entrypoint"]
CMD ["apache2-foreground"]
