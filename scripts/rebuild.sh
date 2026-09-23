#!/bin/sh
set -eu
docker compose build --pull
docker compose up -d
# Containers recriados ganham IP novo; o nginx precisa re-resolver os upstreams.
docker compose exec -T nginx nginx -s reload
