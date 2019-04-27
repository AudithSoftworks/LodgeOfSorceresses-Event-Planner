#!/usr/bin/env bash

#docker-compose pull;
docker-compose down;
docker system prune --force;
docker-compose up -d;
docker-compose ps;

test -f .env || cat .env.example | tee .env > /dev/null 2>&1;

docker-compose exec nginx bash -c "cat /etc/hosts | sed s/localhost/localhost\ planner.lodgeofsorceresses.test/g | tee /etc/hosts";

docker-compose exec php bash -c "
    crontab -l;
    npm config set "@fortawesome:registry" https://npm.fontawesome.com/ && \
    npm config set "//npm.fontawesome.com/:_authToken" ${FONTAWESOME_AUTH_TOKEN}
    npm install;

    cd \$WORKDIR;
    if [[ -d ./node_modules/.google-fonts ]]; then
        cd ./node_modules/.google-fonts && git pull origin master;
    else
        git clone --depth=1 https://github.com/google/fonts.git ./node_modules/.google-fonts;
    fi;

    cd \$WORKDIR;
    rm -rf ./public/fonts/*;
    cp -r ./node_modules/.google-fonts/apache/opensans ./public/fonts/opensans;
    cp -r ./node_modules/.google-fonts/apache/robotocondensed ./public/fonts/robotocondensed;
    cp -r ./node_modules/.google-fonts/ofl/asapcondensed ./public/fonts/asapcondensed;

    convertFonts.sh --use-font-weight --output=public/fonts/asapcondensed/stylesheet.css public/fonts/asapcondensed/*.ttf;
    convertFonts.sh --use-font-weight --output=public/fonts/opensans/stylesheet.css public/fonts/opensans/*.ttf;
    convertFonts.sh --use-font-weight --output=public/fonts/robotocondensed/stylesheet.css public/fonts/robotocondensed/*.ttf;

    npm run build;
    composer install --prefer-source --no-interaction;

    ./artisan key:generate;
    ./artisan migrate;
    ./artisan passport:keys;
    ./artisan db:seed;

    ./vendor/bin/phpunit --debug --verbose --testsuite='Integration';
#    ./artisan dusk -vvv;
#    ./vendor/bin/phpcov merge ./storage/coverage --html ./storage/coverage/merged/;
";
