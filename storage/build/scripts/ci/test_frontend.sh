#!/usr/bin/env bash

echo ">>> CHECKING if MariaDb is ready:";
until docker-compose exec mariadb mysql -D basis -e "SELECT 1" > /dev/null 2>&1; do
    echo "waiting...";
    sleep 1;
done;

docker-compose exec php bash -c "
    ./artisan key:generate;
    ./artisan passport:keys;
    ./artisan migrate;
    ./artisan db:seed;
    ./artisan pmg:skills;
    ./artisan pmg:sets;
    ./artisan cypress:fixture:populate;

    npx cypress run \
      --ci-build-id=${TRAVIS_BUILD_NUMBER} \
      --browser=${BROWSER} \
      --group=${BROWSER} \
      --parallel \
      --record \
      --key ${CYPRESS_KEY} \
      || exit 1;
";
