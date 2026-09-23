#!/bin/sh
set -eu
# Renova certificados Let's Encrypt já emitidos e recarrega o nginx.
# Sugestão de cron (host): 0 3 * * * cd /root/infra-abrasil && ./scripts/renew-certs.sh >> /var/log/certbot-renew.log 2>&1
cd "$(dirname "$0")/.."
docker compose --profile certbot run --rm certbot renew --webroot -w /var/www/certbot
docker compose exec nginx nginx -s reload
