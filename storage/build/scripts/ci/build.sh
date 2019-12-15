#!/usr/bin/env bash

test -f .env || sed \
    -e "s/APP_ENV=.*/APP_ENV=testing/g" \
    -e "s/DB_CONNECTION=.*/DB_CONNECTION=${DB_CONNECTION}/g" \
    -e "s/DB_HOST=.*/DB_HOST=${DB_HOST}/g" \
    -e "s/DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME}/g" \
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
    export NPM_CONFIG_LOGLEVEL=warn;

    crontab -l;
    sudo chown -R audith:audith ./;

    npm config set "@fortawesome:registry" https://npm.fontawesome.com/ && \
    npm config set "//npm.fontawesome.com/:_authToken" ${FONTAWESOME_AUTH_TOKEN}
    npm ci;

    cd \$WORKDIR;
    if [[ -d ~/.cache/google-fonts/.git ]]; then
        cd ~/.cache/google-fonts && git pull origin master;
    else
        rm -rf ~/.cache/google-fonts;
        git clone --depth=1 https://github.com/google/fonts.git ~/.cache/google-fonts;
    fi;

    cd \$WORKDIR;
    rm -rf ./public/fonts/*;
    cp -r ~/.cache/google-fonts/apache/opensans ./public/fonts/opensans;
    cp -r ~/.cache/google-fonts/apache/robotocondensed ./public/fonts/robotocondensed;
    cp -r ~/.cache/google-fonts/ofl/asapcondensed ./public/fonts/asapcondensed;
    cp -r ./resources/fonts/oblivion ./public/fonts/oblivion;
    cp -r ./resources/fonts/oblivion-script ./public/fonts/oblivion-script;
    cp -r ./resources/fonts/planewalker ./public/fonts/planewalker;
    cp -r ./resources/fonts/skyrim-daedra ./public/fonts/skyrim-daedra;
    cp -r ./resources/fonts/sovngarde ./public/fonts/sovngarde;

    convertFonts.sh --use-font-weight --output=public/fonts/asapcondensed/stylesheet.css public/fonts/asapcondensed/*.ttf;
    convertFonts.sh --use-font-weight --output=public/fonts/opensans/stylesheet.css public/fonts/opensans/*.ttf;
    convertFonts.sh --use-font-weight --output=public/fonts/robotocondensed/stylesheet.css public/fonts/robotocondensed/*.ttf;
    convertFonts.sh --use-font-weight --output=public/fonts/oblivion/stylesheet.css public/fonts/oblivion/*.ttf;
    convertFonts.sh --use-font-weight --output=public/fonts/oblivion-script/stylesheet.css public/fonts/oblivion-script/*.ttf;
    convertFonts.sh --use-font-weight --output=public/fonts/planewalker/stylesheet.css public/fonts/planewalker/*.otf;
    convertFonts.sh --use-font-weight --output=public/fonts/skyrim-daedra/stylesheet.css public/fonts/skyrim-daedra/*.ttf;
    convertFonts.sh --use-font-weight --output=public/fonts/sovngarde/stylesheet.css public/fonts/sovngarde/*.ttf;

    mkdir -p ~/.ssh && touch ~/.ssh/known_hosts && chmod 0600 ~/.ssh/known_hosts;
    ssh-keyscan -H github.com >> ~/.ssh/known_hosts;
    NODE_ENV=development npm run build;
";

echo ">>> WAITING for DB to get ready...";
until docker-compose exec mariadb mysql -D basis -e "SHOW TABLES;" > /dev/null 2>&1; do
    sleep 1;
done;

docker-compose exec php bash -c "
    composer install --prefer-source --no-interaction;

    ./artisan key:generate;
    ./artisan passport:keys;
    ./artisan migrate;
    ./artisan db:seed;
    ./artisan pmg:skills;
    ./artisan pmg:sets;
    ./artisan fixture:populate;

    ./vendor/bin/phpunit --debug --verbose --testsuite='Integration';
#    ./artisan dusk -vvv;
#    ./vendor/bin/phpcov merge ./storage/coverage --clover ./storage/coverage/coverage-clover-merged.xml
#    ./vendor/bin/phpunit --debug --verbose --no-coverage --testsuite='SauceWebDriver';
    npx cypress run --record --key ${CYPRESS_KEY};
";
