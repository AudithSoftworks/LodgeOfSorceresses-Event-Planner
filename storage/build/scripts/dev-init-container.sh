#!/usr/bin/env bash
set -e

sudo $(dirname "$0")/dev-permission-fix-and-run.sh $MYUID $MYGID $@
