FROM php:8.1-fpm

ARG BUILD_NUMBER=0.0.0.1
ENV BUILD_NUMBER=${BUILD_NUMBER:-0}

# Set working directory
WORKDIR /var/www

# Add docker php ext repo
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Clean and update apt
RUN apt-get clean && apt-get update

# Install php extensions
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mbstring pdo_mysql zip exif pcntl gd memcached gmp bcmath

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    unzip \
    git \
    curl \
    lua-zlib-dev \
    libmemcached-dev \
    nginx

# Install supervisor
RUN apt-get install -y supervisor

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy code . to /var/www
COPY --chown=www-data:www-data . .

# add root to www group
RUN chmod -R ug+w /var/www/storage

# Copy nginx/php/supervisor configs
COPY ./docker/supervisor.conf /etc/supervisord.conf
COPY ./docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY ./docker/nginx.conf /etc/nginx/sites-enabled/default
COPY ./docker/run.sh /var/www/run.sh

# PHP Error Log Files
RUN mkdir /var/log/php
RUN touch /var/log/php/errors.log && chmod 777 /var/log/php/errors.log

# Deployment steps
RUN composer install --optimize-autoloader #--no-dev
RUN chmod +x /var/www/run.sh

RUN php artisan openapi:generate > /var/www/public/openapi.json

RUN echo "Build number: ${BUILD_NUMBER}" > /var/www/public/version.json

EXPOSE 80
ENTRYPOINT ["/var/www/run.sh"]
