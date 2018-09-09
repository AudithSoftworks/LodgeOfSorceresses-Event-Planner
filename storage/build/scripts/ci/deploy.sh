#!/usr/bin/env bash

if [[ ${DB_CONNECTION} != 'mysql' ]]; then exit 0; fi

sed -e "s/APP_ENV=.*/APP_ENV=production/g" \
    -e "s@APP_KEY=.*@APP_KEY=$APP_KEY@g" \
    -e "s/APP_DEBUG=.*/APP_DEBUG=false/g" \
    -e "s/APP_LOG_LEVEL=.*/APP_LOG_LEVEL=error/g" \
    -e "s/REDIS_HOST=.*/REDIS_HOST=${REDIS_HOST}/g" \
    -e "s/DB_HOST=.*/DB_HOST=${DB_HOST_LIVE}/g" \
    -e "s/DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE_LIVE}/g" \
    -e "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD_LIVE}/g" \
    -e "s/DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME_LIVE}/g" \
    -e "s/IPS_CLIENT_ID=.*/IPS_CLIENT_ID=${IPS_CLIENT_ID}/g" \
    -e "s/IPS_CLIENT_SECRET=.*/IPS_CLIENT_SECRET=${IPS_CLIENT_SECRET}/g" \
    -e "s/IPS_API_KEY=.*/IPS_API_KEY=${IPS_API_KEY}/g" .env.example \
    | tee .env;

rm -rf ./.git* ./node_modules/ ./tests/ ./deploy_rsa.enc ./.scrutinizer.yml ./.travis.yml ./_ide_helper.php old;

rsync -r --delete-after -e "ssh -o StrictHostKeyChecking=no" ${TRAVIS_BUILD_DIR}/ lodgeofsorceresses@lodgeofsorceresses.com:/opt/lodgeofsorceresses/subdomains/events/${TRAVIS_JOB_ID}/
