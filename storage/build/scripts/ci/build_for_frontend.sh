#!/usr/bin/env bash

export NODE_ENV='development';
if [ "$1" == 'production' ]; then
    export NODE_ENV='production';
fi;

docker-compose exec php bash -c "
    export NPM_CONFIG_LOGLEVEL=warn;

    npm ci;

    npx browserslist@latest --update-db;

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

    NODE_ENV=${NODE_ENV} npm run build;
";
