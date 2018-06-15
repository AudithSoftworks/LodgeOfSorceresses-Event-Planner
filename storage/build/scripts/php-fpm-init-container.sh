#!/usr/bin/env bash
set -e

if [[ $(id -g www-data) != $MYGID || $(id -u www-data) != $MYUID ]]; then
    groupmod -g $MYGID www-data
    usermod -u $MYUID -g $MYGID www-data
fi

docker-php-entrypoint "$@"
