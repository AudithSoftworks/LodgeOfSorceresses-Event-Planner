#!/usr/bin/env bash
set -e

# this script must be called with root permissions
if [[ $(id -g audith) != $MYGID || $(id -u audith) != $MYUID ]]; then
    sudo groupmod -g $MYGID audith
    sudo usermod -u $MYUID -g $MYGID audith
fi;

sudo mkdir -p /home/audith
sudo chown -R audith:audith /home/audith

docker-php-entrypoint "$@"

