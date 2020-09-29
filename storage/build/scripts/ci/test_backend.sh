#!/usr/bin/env bash

sed \
    -e "s/APP_ENV=.*/APP_ENV=testing/g" \
    -e "s/DB_CONNECTION=.*/DB_CONNECTION=pgsql/g" \
    -e "s/DB_HOST=.*/DB_HOST=postgres/g" \
    -e "s/DB_USERNAME=.*/DB_USERNAME=postgres/g" \
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
    dockerize -wait tcp://postgres:5432 -timeout 30s echo \"Postgres ready for connections...\";

    ./artisan key:generate;
    ./artisan passport:keys;
    ./artisan migrate;
    ./artisan db:seed;
    ./artisan pmg:skills;
    ./artisan pmg:sets;
    ./artisan optimize:clear;

    ./vendor/bin/phpunit --no-coverage --debug --verbose || exit 1;
";

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
    dockerize -wait tcp://mariadb:3306 -timeout 30s echo \"MariaDb ready for connections...\";

    ./artisan key:generate;
    ./artisan passport:keys;
    ./artisan migrate;
    ./artisan db:seed;
    ./artisan pmg:skills;
    ./artisan pmg:sets;
    ./artisan optimize:clear;

    ./vendor/bin/phpunit --debug --verbose || exit 1;

    COVERALLS_REPO_TOKEN=${COVERALLS_REPO_TOKEN} ./vendor/bin/php-coveralls -v
";
