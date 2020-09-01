#!/usr/bin/env bash

cd /opt/lodge-planner/www/$1;
chmod u+rwx ./storage/logs;

echo "Clearing cached bootstrap files..." && ./artisan optimize:clear;
echo "Attempting Db migrations..." && ./artisan migrate --force;
echo "Seeding Db..." && ./artisan db:seed --force;
echo "Renewing Skills-list from PMG..." && ./artisan pmg:skills;
echo "Renewing Sets-list from PMG..." && ./artisan pmg:sets;
echo "Caching Configuration..." && ./artisan config:cache;
echo "Caching Routes..." && ./artisan route:cache;
echo "Linking Storage..." && ./artisan storage:link;
echo "Generating Passport keys..." && ./artisan passport:keys;
echo "Cache warm-up..." && ./artisan cache:warmup;

composer install --prefer-source --no-interaction --no-dev;

touch ./public/$1.php;
echo "<?php opcache_reset(); unlink(__FILE__);" | tee ./public/$1.php;
rm /opt/lodge-planner/www/current;
ln -s /opt/lodge-planner/www/$1 /opt/lodge-planner/www/current;
wget -q https://planner.lodgeofsorceresses.com/$1.php;
