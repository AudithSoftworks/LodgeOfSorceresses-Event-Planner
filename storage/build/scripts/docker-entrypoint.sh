#!/usr/bin/env bash
set -e

[[ -n "${SQREEN_TOKEN}" ]] && sudo sqreen-installer config "${SQREEN_TOKEN}" "${SQREEN_APP_NAME}";

sudo chmod +x ./"$(dirname "$0")"/fix-container-uid-gid.sh
sudo ./"$(dirname "$0")"/fix-container-uid-gid.sh "$MYUID" "$MYGID" "$@"

docker-php-entrypoint "$@"
