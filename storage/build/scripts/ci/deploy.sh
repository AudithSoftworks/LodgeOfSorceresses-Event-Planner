#!/usr/bin/env bash

sed -e "s/APP_ENV=.*/APP_ENV=production/g" \
    -e "s@APP_KEY=.*@APP_KEY=${APP_KEY}@g" \
    -e "s/APP_DEBUG=.*/APP_DEBUG=false/g" \
    -e "s/APP_LOG_LEVEL=.*/APP_LOG_LEVEL=info/g" \
    -e "s@REDIS_HOST=.*@REDIS_HOST=${REDIS_HOST}@g" \
    -e "s@DB_HOST=.*@DB_HOST=${DB_HOST_LIVE}@g" \
    -e "s/DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE_LIVE}/g" \
    -e "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD_LIVE}/g" \
    -e "s/DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME_LIVE}/g" \
    -e "s/IPS_CLIENT_ID=.*/IPS_CLIENT_ID=${IPS_CLIENT_ID}/g" \
    -e "s/IPS_CLIENT_SECRET=.*/IPS_CLIENT_SECRET=${IPS_CLIENT_SECRET}/g" \
    -e "s/IPS_API_KEY=.*/IPS_API_KEY=${IPS_API_KEY}/g" \
    -e "s/CLOUDINARY_CLOUD_NAME=.*/CLOUDINARY_CLOUD_NAME=${CLOUDINARY_CLOUD_NAME}/g" \
    -e "s/CLOUDINARY_API_KEY=.*/CLOUDINARY_API_KEY=${CLOUDINARY_API_KEY}/g" \
    -e "s/CLOUDINARY_API_SECRET=.*/CLOUDINARY_API_SECRET=${CLOUDINARY_API_SECRET}/g" \
    -e "s/DISCORD_CLIENT_ID=.*/DISCORD_CLIENT_ID=${DISCORD_CLIENT_ID}/g" \
    -e "s/DISCORD_CLIENT_SECRET=.*/DISCORD_CLIENT_SECRET=${DISCORD_CLIENT_SECRET}/g" \
    -e "s/DISCORD_ANNOUNCEMENTS_CHANNEL_ID=.*/DISCORD_ANNOUNCEMENTS_CHANNEL_ID=${DISCORD_ANNOUNCEMENTS_CHANNEL_ID}/g" \
    -e "s/DISCORD_ACHIEVEMENTS_CHANNEL_ID=.*/DISCORD_ACHIEVEMENTS_CHANNEL_ID=${DISCORD_ACHIEVEMENTS_CHANNEL_ID}/g" \
    -e "s/DISCORD_LOOKING_FOR_CHANNEL_ID=.*/DISCORD_LOOKING_FOR_CHANNEL_ID=${DISCORD_LOOKING_FOR_CHANNEL_ID}/g" \
    -e "s/DISCORD_SUBSCRIPTIONS_CHANNEL_ID=.*/DISCORD_SUBSCRIPTIONS_CHANNEL_ID=${DISCORD_SUBSCRIPTIONS_CHANNEL_ID}/g" \
    -e "s/DISCORD_DPS_PARSES_CHANNEL_ID=.*/DISCORD_DPS_PARSES_CHANNEL_ID=${DISCORD_DPS_PARSES_CHANNEL_ID}/g" \
    -e "s/DISCORD_PVE_OPEN_EVENTS_CHANNEL_ID=.*/DISCORD_PVE_OPEN_EVENTS_CHANNEL_ID=${DISCORD_PVE_OPEN_EVENTS_CHANNEL_ID}/g" \
    -e "s/DISCORD_PVE_CORE_ANNOUNCEMENTS_CHANNEL_ID=.*/DISCORD_PVE_CORE_ANNOUNCEMENTS_CHANNEL_ID=${DISCORD_PVE_CORE_ANNOUNCEMENTS_CHANNEL_ID}/g" \
    -e "s/DISCORD_OFFICER_HQ_CHANNEL_ID=.*/DISCORD_OFFICER_HQ_CHANNEL_ID=${DISCORD_OFFICER_HQ_CHANNEL_ID}/g" \
    -e "s/DISCORD_OFFICER_LOGS_CHANNEL_ID=.*/DISCORD_OFFICER_LOGS_CHANNEL_ID=${DISCORD_OFFICER_LOGS_CHANNEL_ID}/g" \
    -e "s/DISCORD_BOT_TOKEN=.*/DISCORD_BOT_TOKEN=${DISCORD_BOT_TOKEN}/g" \
    -e "s/GOOGLE_API_YOUTUBE_DATA_API_KEY=.*/GOOGLE_API_YOUTUBE_DATA_API_KEY=${GOOGLE_API_YOUTUBE_DATA_API_KEY}/g" \
    -e "s/PMG_API_TOKEN=.*/PMG_API_TOKEN=${PMG_API_TOKEN}/g" \
    .env.example | tee .env > /dev/null 2>&1;

rm -rf ./.git* ./node_modules ./storage/build/tools ./storage/coverage ./tests ./deploy_rsa.enc ./.scrutinizer.yml ./.travis.yml ./_ide_helper.php old;

rsync -r --delete-after -e "ssh -o StrictHostKeyChecking=no" ${TRAVIS_BUILD_DIR}/ lodgeofsorceresses@lodgeofsorceresses.com:/opt/lodgeofsorceresses/subdomains/planner/${$TRAVIS_BUILD_NUMBER}/
