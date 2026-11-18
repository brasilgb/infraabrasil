#!/bin/sh
set -eu
# Renova certificados Let's Encrypt já emitidos e recarrega o nginx.
# Cron (host): 0 3 * * * /opt/infra-abrasil/scripts/renew-certs.sh >> /var/log/certbot-renew.log 2>&1
cd "$(dirname "$0")/.."
# Sem --webroot: cada certificado usa o autenticador gravado em renewal/*.conf
# (webroot para os atuais; DNS-01 com hook para o curinga do VetorSmart).
docker compose --profile certbot run --rm certbot renew
docker compose exec nginx nginx -s reload
