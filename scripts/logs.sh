#!/bin/sh
set -eu
if [ "$#" -gt 0 ]; then
    docker compose logs --tail=100 "$@"
else
    docker compose logs --tail=100
fi
