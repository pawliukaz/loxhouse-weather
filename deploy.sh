#!/usr/bin/env bash
mkdir /var/www/releases/release
git clone git@github.com:pawliukaz/loxhouse-weather.git /var/www/releases/release
cd /var/www/releases/release && ln -s /var/www/shared/.env .env
cd /var/www/releases/release && composer install
cd /var/www/releases/release && yarn install prodution
