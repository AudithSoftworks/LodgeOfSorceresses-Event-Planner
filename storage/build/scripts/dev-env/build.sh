#!/usr/bin/env bash

#docker build -f storage/build/scripts/nginx/Dockerfile -t audithsoftworks/basis:nginx .
#docker build -f storage/build/scripts/php_7/Dockerfile -t audithsoftworks/basis:php_7 .;
#docker build -f storage/build/scripts/php_7-fpm/Dockerfile -t audithsoftworks/basis:php_7-fpm .;

#docker-compose build
docker-compose pull;

docker-compose down;
docker-compose up -d;
docker-compose ps;

test -f .env || cat .env.example | tee .env > /dev/null 2>&1;

###############################################################################################################
# IMPORTANT NOTE: Before running the next command, make sure you have also exported SAUCE_USERNAME
# and SAUCE_ACCESS_KEY env variables to the environment for which the next 'docker exec' is being run.
###############################################################################################################

docker-compose exec dev-env bash -c "
    sudo mkdir -p ~;
    sudo chown -R basis:basis ~;

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
    cp -r ./node_modules/bootstrap-sass/assets/fonts/bootstrap ./public/fonts/glyphicons;
    cp -r ./node_modules/font-awesome/fonts ./public/fonts/font_awesome;
    cp -r ./node_modules/simple-line-icons-webfont/fonts ./public/fonts/simple-line-icons;
    cp -r ./node_modules/.google-fonts/apache/opensans ./public/fonts/opensans;
    cp -r ./node_modules/.google-fonts/apache/robotocondensed ./public/fonts/robotocondensed;
    cp -r ./node_modules/.google-fonts/ofl/asapcondensed ./public/fonts/asapcondensed;
    cp -r ./node_modules/.google-fonts/ofl/marcellus ./public/fonts/marcellus;
    cp -r ./node_modules/.google-fonts/ofl/montserrat ./public/fonts/montserrat;
    cp -r ./node_modules/.google-fonts/ofl/pontanosans ./public/fonts/pontano_sans;

    chmod -R +x /var/www/storage/build/tools;
    ./storage/build/tools/css3_font_converter/convertFonts.sh --use-font-weight --output=public/fonts/simple-line-icons/stylesheet.css public/fonts/simple-line-icons/*.ttf;
    ./storage/build/tools/css3_font_converter/convertFonts.sh --use-font-weight --output=public/fonts/asapcondensed/stylesheet.css public/fonts/asapcondensed/*.ttf;
    ./storage/build/tools/css3_font_converter/convertFonts.sh --use-font-weight --output=public/fonts/opensans/stylesheet.css public/fonts/opensans/*.ttf;
    ./storage/build/tools/css3_font_converter/convertFonts.sh --use-font-weight --output=public/fonts/robotocondensed/stylesheet.css public/fonts/robotocondensed/*.ttf;

    npm run build;
    composer update --prefer-source --no-interaction;

    ./artisan key:generate;
    ./artisan migrate;
    ./artisan passport:install;

    sudo chown -R 1000:1000 ./;
    sudo chmod -R 0777 ./storage/framework/views/twig;
    sudo chmod -R 0777 ./storage/logs;

    ./vendor/bin/phpunit --debug --verbose --testsuite='Unit';
    ./artisan dusk -vvv;

    ./vendor/bin/phpcov merge ./storage/coverage --html ./storage/coverage/merged/;
";

#stty cols 239 rows 61;
#docker-compose down;
