#!/usr/bin/env bash

cd /opt/lodgeofsorceresses/subdomains/events/$1;

composer install --prefer-source --no-interaction --no-dev;

./artisan migrate:refresh --force;
./artisan db:seed --force;
./artisan config:cache;
./artisan route:cache;
./artisan storage:link

touch ./public/$1.php;
echo "<?php opcache_reset(); unlink(__FILE__);" | tee ./public/$1.php;
rm /opt/lodgeofsorceresses/subdomains/events/current;
ln -s /opt/lodgeofsorceresses/subdomains/events/$1 /opt/lodgeofsorceresses/subdomains/events/current;
wget -q http://events.lodgeofsorceresses.com/$1.php
