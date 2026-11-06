#!/usr/bin/env bash

php-fpm -D

/usr/sbin/nginx -g "daemon off;"
