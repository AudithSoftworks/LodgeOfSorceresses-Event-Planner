#!/usr/bin/env bash

docker-compose pull;
docker-compose down;
docker system prune --force --volumes;
docker-compose up -d;
docker-compose ps;

test -f .env || cat .env.example | tee .env > /dev/null 2>&1;

docker-compose exec php bash -c "
    crontab -l;
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

    npm run build;
";

echo ">>> WAITING for DB to get ready...";
until docker-compose exec mariadb mysql -D basis -e "SHOW TABLES;" > /dev/null 2>&1; do
    sleep 1;
done;

docker-compose exec php bash -c "
    composer install --prefer-source --no-interaction;

    ./artisan key:generate;
    ./artisan migrate;
    ./artisan passport:keys;
    ./artisan db:seed;
    ./artisan pmg:skills;
    ./artisan pmg:sets;
    ./artisan cache:warmup;
    ./artisan fixture:populate;
    ./artisan optimize:clear;

    ./vendor/bin/phpunit --debug --verbose;
    npx cypress run;
";
