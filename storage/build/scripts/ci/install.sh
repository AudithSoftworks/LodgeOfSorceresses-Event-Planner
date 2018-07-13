#!/usr/bin/env bash

cd /opt/lodgeofsorceresses/subdomains/events/$1;

composer install --prefer-source --no-interaction --no-dev;

./artisan migrate:refresh --force;
./artisan db:seed --force;
./artisan config:cache;
./artisan route:cache;

rm /opt/lodgeofsorceresses/subdomains/events/current;
ln -s /opt/lodgeofsorceresses/subdomains/events/$1 /opt/lodgeofsorceresses/subdomains/events/current;
