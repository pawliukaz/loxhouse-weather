#!/usr/bin/env bash
mkdir /var/www/releases/release
git clone git@github.com:pawliukaz/loxhouse-weather.git /var/www/releases/release
cd /var/www/releases/release && ln -s /var/www/shared/.env .env
cd /var/www/releases/release && mkdir var
cd /var/www/releases/release && ln -s /var/www/shared/var/log var/log
cd /var/www/releases/release && chmod 777 var/
cd /var/www/releases/release && composer install
cd /var/www/releases/release && yarn && yarn encore production
cd /var/www/releases/release && yarn encore production
dateValue=$(date +%Y%m%d%H%M%S)
cd /var/www/releases && mkdir $dateValue
cd /var/www/releases && mv release/* $dateValue
ln -sfn /var/www/releases/$dateValue /var/www/current
rm -rf /var/www/releases/release