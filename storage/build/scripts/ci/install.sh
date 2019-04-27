#!/usr/bin/env bash

cd /opt/lodgeofsorceresses/subdomains/planner/$1;

./artisan migrate --force;
./artisan pmg:skills;
./artisan crawler:sets;
./artisan config:cache;
./artisan route:cache;
./artisan storage:link

composer install --prefer-source --no-interaction --no-dev;

touch ./public/$1.php;
echo "<?php opcache_reset(); unlink(__FILE__);" | tee ./public/$1.php;
rm /opt/lodgeofsorceresses/subdomains/planner/current;
ln -s /opt/lodgeofsorceresses/subdomains/planner/$1 /opt/lodgeofsorceresses/subdomains/planner/current;
wget -q http://planner.lodgeofsorceresses.com/$1.php
