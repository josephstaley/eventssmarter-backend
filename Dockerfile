FROM php:8.1-fpm

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libmcrypt-dev \
    curl \
    openssl \
    zip \
    unzip \
    git \
    libmagickwand-dev --no-install-recommends \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install -j$(nproc) pdo \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql mbstring zip exif pcntl \
    && docker-php-ext-install  bcmath \
    && docker-php-ext-install  zip \
    && docker-php-ext-install opcache

# Install the Imagick PHP extension
RUN pecl install imagick \
    && docker-php-ext-enable imagick

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

ADD ./deploy/opcache.ini "$PHP_INI_DIR/conf.d/opcache.ini"

#RUN apt-get install supervisor -y

RUN apt-get install -y nginx  && \
    rm -rf /var/lib/apt/lists/*

# Prestissimo is a spiffing plugin
#RUN composer global require hirak/prestissimo --no-plugins --no-scripts

WORKDIR /var/www/html

# Install dependencies
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-scripts --no-autoloader

# Copy codebase
COPY . .
COPY .env.production .env
RUN chmod 644 .env

# Finish composer
RUN composer dump-autoload --optimize --no-scripts

# Set up Node.js environment for Vite (using multi-stage build)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Install node modules
RUN npm install

# Step 8: Run Vite build
RUN npm run build

#Copy Nginx configuration
RUN rm /etc/nginx/sites-enabled/default

COPY ./deploy/nginx.conf /etc/nginx/nginx.conf
COPY ./deploy/deploy.conf /etc/nginx/conf.d/default.conf

RUN mv /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf.backup
COPY ./deploy/www.conf /usr/local/etc/php-fpm.d/www.conf
#COPY ./deploy/supervisord.conf /etc/supervisord.conf

RUN usermod -a -G www-data root
RUN chgrp -R www-data storage

RUN chown -R www-data:www-data ./storage
RUN chmod -R 755 ./storage

RUN chmod +x ./deploy/run

# create a Symlink that references  your  error log
RUN ln -s /app/storage/logs /opt/logs

ENTRYPOINT ["./deploy/run"]

EXPOSE 80
