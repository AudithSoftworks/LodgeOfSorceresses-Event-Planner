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
    export NPM_CONFIG_LOGLEVEL=warn;
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
    if [[ ! -d ./public/fonts/opensans ]]; then
        cp -r ~/.cache/google-fonts/apache/opensans ./public/fonts/opensans;
        convertFonts.sh --use-font-weight --output=public/fonts/opensans/stylesheet.css public/fonts/opensans/*.ttf;
    fi;
    if [[ ! -d ./public/fonts/asapcondensed ]]; then
        cp -r ~/.cache/google-fonts/ofl/asapcondensed ./public/fonts/asapcondensed;
        convertFonts.sh --use-font-weight --output=public/fonts/asapcondensed/stylesheet.css public/fonts/asapcondensed/*.ttf;
    fi;
    if [[ ! -d ./public/fonts/oblivion ]]; then
        cp -r ./resources/fonts/oblivion ./public/fonts/oblivion;
        convertFonts.sh --use-font-weight --output=public/fonts/oblivion/stylesheet.css public/fonts/oblivion/*.ttf;
    fi;
    if [[ ! -d ./public/fonts/oblivion-script ]]; then
        cp -r ./resources/fonts/oblivion-script ./public/fonts/oblivion-script;
        convertFonts.sh --use-font-weight --output=public/fonts/oblivion-script/stylesheet.css public/fonts/oblivion-script/*.ttf;
    fi;
    if [[ ! -d ./public/fonts/planewalker ]]; then
        cp -r ./resources/fonts/planewalker ./public/fonts/planewalker;
        convertFonts.sh --use-font-weight --output=public/fonts/planewalker/stylesheet.css public/fonts/planewalker/*.otf;
    fi;
    if [[ ! -d ./public/fonts/skyrim-daedra ]]; then
        cp -r ./resources/fonts/skyrim-daedra ./public/fonts/skyrim-daedra;
        convertFonts.sh --use-font-weight --output=public/fonts/skyrim-daedra/stylesheet.css public/fonts/skyrim-daedra/*.ttf;
    fi;
    if [[ ! -d ./public/fonts/sovngarde ]]; then
        cp -r ./resources/fonts/sovngarde ./public/fonts/sovngarde;
        convertFonts.sh --use-font-weight --output=public/fonts/sovngarde/stylesheet.css public/fonts/sovngarde/*.ttf;
    fi;

    NODE_ENV=development npm run build;
";

docker-compose exec php bash -c "
    dockerize -wait tcp://mariadb:3306 echo \"MariaDb ready for connections...\";

    composer install --prefer-source --no-interaction;
";
