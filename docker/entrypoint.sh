#!/usr/bin/env bash
set -e

mkdir -p \
  /var/www/html/storage/logs \
  /var/www/html/storage/uploads \
  /var/www/html/public/uploads

touch \
  /var/www/html/storage/logs/access.log \
  /var/www/html/storage/logs/security.log \
  /var/www/html/storage/logs/application.log \
  /var/www/html/storage/logs/apache-access.log \
  /var/www/html/storage/logs/apache-error.log \
  /var/www/html/storage/logs/php-error.log

chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads || true

exec "$@"
