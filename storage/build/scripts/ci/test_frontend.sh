#!/usr/bin/env bash

docker-compose exec php bash -c "
    dockerize -timeout 30s \
      -wait tcp://mariadb:3306 \
      -wait tcp://redis:6379 \
      -wait http://planner.lodgeofsorceresses.test:80 \
      echo \"All containers ready...\";

    ./artisan key:generate;
    ./artisan passport:keys;
    ./artisan migrate;
    ./artisan db:seed;
    ./artisan pmg:skills;
    ./artisan pmg:sets;
    ./artisan optimize:clear;
    ./artisan cypress:fixture:populate;
";

docker-compose exec cypress bash -c "
    cypress run --ci-build-id=${TRAVIS_BUILD_NUMBER} --browser=${BROWSER} --group=${BROWSER} --record --parallel --key ${CYPRESS_KEY};
";
