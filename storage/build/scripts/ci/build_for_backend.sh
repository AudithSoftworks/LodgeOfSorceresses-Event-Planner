#!/usr/bin/env bash

sed \
    -e "s/APP_ENV=.*/APP_ENV=testing/g" \
    -e "s/IPS_CLIENT_ID=.*/IPS_CLIENT_ID=${IPS_CLIENT_ID}/g" \
    -e "s/IPS_CLIENT_SECRET=.*/IPS_CLIENT_SECRET=${IPS_CLIENT_SECRET}/g" \
    -e "s/IPS_API_KEY=.*/IPS_API_KEY=${IPS_API_KEY}/g" \
    -e "s/CLOUDINARY_CLOUD_NAME=.*/CLOUDINARY_CLOUD_NAME=${CLOUDINARY_CLOUD_NAME}/g" \
    -e "s/CLOUDINARY_API_KEY=.*/CLOUDINARY_API_KEY=${CLOUDINARY_API_KEY}/g" \
    -e "s/CLOUDINARY_API_SECRET=.*/CLOUDINARY_API_SECRET=${CLOUDINARY_API_SECRET}/g" \
    -e "s/DISCORD_CLIENT_ID=.*/DISCORD_CLIENT_ID=${DISCORD_CLIENT_ID}/g" \
    -e "s/DISCORD_CLIENT_SECRET=.*/DISCORD_CLIENT_SECRET=${DISCORD_CLIENT_SECRET}/g" \
    -e "s/DISCORD_BOT_TOKEN=.*/DISCORD_BOT_TOKEN=${DISCORD_BOT_TOKEN}/g" \
    -e "s/PMG_API_TOKEN=.*/PMG_API_TOKEN=${PMG_API_TOKEN}/g" \
    .env.example | tee .env > /dev/null 2>&1;

docker-compose exec php bash -c "
    dockerize -wait tcp://mariadb:3306 echo \"MariaDb ready for connections...\";

    composer install --prefer-source --no-interaction;
";
