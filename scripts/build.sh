#!/usr/bin/env bash

echo "build"

#basic configuration
yum update -y
sudo amazon-linux-extras enable epel
yum install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm
sudo yum update -y
yum install yum-utils -y
yum-config-manager --disable 'remi-php*'
yum-config-manager --enable   remi-php81
yum clean metadata
yum update -y php php-cli php-intl php-common php-devel php-fpm php-gd php-mbstring php-mysqlnd php-opcache php-pdo php-process php-xml zip unzip php-zip --disableplugin=priorities

#Upload Size
sudo find / -name php.ini
sudo sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 300M/' /etc/php.ini
sudo sed -i 's/^post_max_size = .*/post_max_size = 300M/' /etc/php.ini
#sudo sed -i 's/^max_execution_time = .*/max_execution_time = 100/' /etc/php.ini
sudo sed -i 's/^memory_limit = .*/memory_limit = 300M/' /etc/php.ini

# Install PHP Imagick
echo "Installing imagick packages--------------------------------------------------------------------->>>>>"

sudo yum -y install php-pear php-devel gcc ImageMagick ImageMagick-devel
sudo bash -c "yes '' | pecl install -f imagick"
sudo bash -c "echo 'extension=imagick.so' > /etc/php.d/imagick.ini"
sudo bash -c "echo 'extension=imagick.so' > /etc/php.ini"

echo 'Check imagick installed status'
php -m|grep imagick
echo "Finish imagick packages--------------------------------------------------------------------->>>>>"

# Install Composer & necessary packages
EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
then
    >&2 echo 'ERROR: Invalid installer checksum'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php
rm composer-setup.php

yum install nginx -y

# Install dependencies
php composer.phar install

cp -p .env.production .env
php artisan config:cache
php artisan config:clear
sudo chmod -R 755 ./storage

# Nginx And PHP-FPM Configuration
cp -p /app/scripts/resources/nginx.conf /etc/nginx/nginx.conf
cp -p /app/scripts/resources/www.conf /etc/php-fpm.d/www.conf

# Directory Permission
chown -R :nginx /app/storage
chown -R :nginx /app/bootstrap/cache
chown -R :nginx /app/public

find /app/storage -type d -exec chmod 775 {} \;
find /app/storage -type f -exec chmod 664 {} \;

find /app/bootstrap/cache -type d -exec chmod 775 {} \;
find /app/bootstrap/cache -type f -exec chmod 664 {} \;

find /app/storage -type d -exec chmod g+s {} \;
find /app/bootstrap/cache -type d -exec chmod g+s {} \;

setfacl -R -d -m g::rwx /app/storage
setfacl -R -d -m g::rwx /app/bootstrap/cache

# Log Configuration
ln -s /dev/stdout /var/log/nginx/error.log
ln -s /dev/stdout /var/log/nginx/access.log

ln -s /dev/stdout /var/log/php-fpm/error.log
ln -s /dev/stdout /var/log/php-fpm/www-access.log
ln -s /dev/stdout /var/log/php-fpm/www-error.log

# NPM Build
echo "node js, npm install"
curl -fsSL https://rpm.nodesource.com/setup_16.x | bash - && yum -y install nodejs
yum install gcc-c++ -y

echo "npm install"
npm install

echo "npm build"
npm run build
