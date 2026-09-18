#!/bin/sh
set -eu
docker compose build --pull
docker compose up -d
