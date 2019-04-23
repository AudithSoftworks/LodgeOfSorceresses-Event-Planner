#!/usr/bin/env bash

docker-compose pull;

docker-compose down;
docker-compose up -d;
docker-compose ps;

test -f .env || cat .env.example | tee .env > /dev/null 2>&1;

docker-compose exec nginx bash -c "cat /etc/hosts | sed s/localhost/localhost\ planner.lodgeofsorceresses.test/g | tee /etc/hosts";

docker-compose exec php bash -c "
    crontab -l;
    npm update;

    cd \$WORKDIR;
    if [[ ! -d ./storage/build/tools/woff2 ]]; then
        git clone --depth=1 https://github.com/google/woff2.git ./storage/build/tools/woff2;
        cd /var/www/storage/build/tools/woff2 && git submodule init && git submodule update && make clean all;
    fi;

    cd \$WORKDIR;
    if [[ ! -d ./storage/build/tools/css3_font_converter ]]; then
        git clone --depth=1 https://github.com/zoltan-dulac/css3FontConverter.git ./storage/build/tools/css3_font_converter;
    fi;

    cd \$WORKDIR;
    if [[ -d ./node_modules/.google-fonts ]]; then
        cd ./node_modules/.google-fonts && git pull origin master;
    else
        git clone --depth=1 https://github.com/google/fonts.git ./node_modules/.google-fonts;
    fi;

    cd \$WORKDIR;
    rm -rf ./public/fonts/*;
    cp -r ./node_modules/simple-line-icons-webfont/fonts ./public/fonts/simple-line-icons;
    cp -r ./node_modules/.google-fonts/apache/opensans ./public/fonts/opensans;
    cp -r ./node_modules/.google-fonts/apache/robotocondensed ./public/fonts/robotocondensed;
    cp -r ./node_modules/.google-fonts/ofl/asapcondensed ./public/fonts/asapcondensed;

    chmod -R +x /var/www/storage/build/tools;
    ./storage/build/tools/css3_font_converter/convertFonts.sh --use-font-weight --output=public/fonts/simple-line-icons/stylesheet.css public/fonts/simple-line-icons/*.ttf;
    ./storage/build/tools/css3_font_converter/convertFonts.sh --use-font-weight --output=public/fonts/asapcondensed/stylesheet.css public/fonts/asapcondensed/*.ttf;
    ./storage/build/tools/css3_font_converter/convertFonts.sh --use-font-weight --output=public/fonts/opensans/stylesheet.css public/fonts/opensans/*.ttf;
    ./storage/build/tools/css3_font_converter/convertFonts.sh --use-font-weight --output=public/fonts/robotocondensed/stylesheet.css public/fonts/robotocondensed/*.ttf;

    npm run build;
    composer install --prefer-source --no-interaction;

    ./artisan key:generate;
    ./artisan migrate;
    ./artisan passport:keys;
    ./artisan db:seed;

#    ./vendor/bin/phpunit --debug --verbose --testsuite='Unit';
#    ./artisan dusk -vvv;
#    ./vendor/bin/phpcov merge ./storage/coverage --html ./storage/coverage/merged/;
";
