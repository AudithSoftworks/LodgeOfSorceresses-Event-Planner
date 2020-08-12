#!/usr/bin/env bash

docker-compose exec php bash -c "
    dockerize -timeout 30s \
      -wait tcp://postgres:5432 \
      -wait tcp://mariadb:3306 \
      -wait tcp://redis:6379 \
      -wait http://planner.lodgeofsorceresses.test:80 \
      echo \"All containers ready for connections...\";

    ./artisan key:generate;
    ./artisan passport:keys;
    ./artisan migrate;
    ./artisan db:seed;
    ./artisan pmg:skills;
    ./artisan pmg:sets;
    ./artisan optimize:clear;
    ./artisan cypress:fixture:populate;

    touch .ready_for_frontend_testing;

    dockerize -timeout 900s \
      -wait file:///var/www/cypress/videos/guest/onboarding/01-start.spec.js.mp4 \
      -wait file:///var/www/cypress/videos/guest/onboarding/11-full-member-workflow.spec.js.mp4 \
      -wait file:///var/www/cypress/videos/guest/onboarding/12-full-soulshriven-workflow.spec.js.mp4 \
      -wait file:///var/www/cypress/videos/guest/soulshriven/01-roster.spec.js.mp4 \
      -wait file:///var/www/cypress/videos/01-login.spec.js.mp4 \
      rm .ready_for_frontend_testing;
";
