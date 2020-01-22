#!/usr/bin/env bash
mkdir /var/www/releases/release
git clone git@github.com:pawliukaz/loxhouse-weather.git /var/www/releases/release
cd /var/www/releases/release && ln -s /var/www/shared/.env .env
cd /var/www/releases/release && mkdir var
cd /var/www/releases/release && chmod 777 -R var/cache
cd /var/www/releases/release && ln -s /var/www/shared/var/log var
cd /var/www/releases/release && APP_ENV=prod composer install --no-dev
cd /var/www/releases/release && yarn && yarn encore production
cd /var/www/releases/release && rm -rf scripts
dateValue=$(date +%Y%m%d%H%M%S)
cd /var/www/releases && mkdir $dateValue
cd /var/www/releases && sudo mv release/.env $dateValue/.env
cd /var/www/releases && sudo mv release/* $dateValue
sudo chown -R www-data:www-data /var/www/releases/$dateValue
ln -sfn /var/www/releases/$dateValue /var/www/current
sudo rm -rf /var/www/releases/release
