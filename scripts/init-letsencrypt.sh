#!/bin/sh
set -eu
# Emite os certificados Let's Encrypt reais para os domínios configurados em .env.
# Passo a passo: gera certificados "dummy" para o nginx conseguir subir com bloco
# ssl, sobe o nginx, pede o certificado real via webroot e recarrega o nginx.
# Cada domínio é processado de forma independente: se um falhar (ex.: DNS ainda
# não propagado), o dummy cert é restaurado para não derrubar o nginx, e o
# script segue para os demais domínios.
cd "$(dirname "$0")/.."

if [ -f .env ]; then
    set -a
    . ./.env
    set +a
fi

: "${CERTBOT_EMAIL:?configure CERTBOT_EMAIL em .env}"
# Domínios apex: certificado cobre também o "www.". Subdomínios (ex.: n8n) não usam "www.".
APEX_DOMAINS="${VETOROS_DOMAIN:-vetoros.com.br} ${VETORPET_DOMAIN:-vetorpet.com.br} ${ABRASILSISTEMA_DOMAIN:-abrasilsistemas.com.br}"
SUBDOMAINS="${N8N_DOMAIN:-n8n.abrasilsistemas.com.br}"
DOMAINS="$APEX_DOMAINS $SUBDOMAINS"

is_apex() {
    for d in $APEX_DOMAINS; do
        [ "$d" = "$1" ] && return 0
    done
    return 1
}

CONF_DIR=./volumes/certbot/conf
WWW_DIR=./volumes/certbot/www
mkdir -p "$WWW_DIR"

make_dummy() {
    domain="$1"
    live_dir="$CONF_DIR/live/$domain"
    mkdir -p "$live_dir"
    openssl req -x509 -nodes -newkey rsa:2048 -days 1 \
        -keyout "$live_dir/privkey.pem" \
        -out "$live_dir/fullchain.pem" \
        -subj "/CN=$domain" >/dev/null 2>&1
}

for domain in $DOMAINS; do
    live_dir="$CONF_DIR/live/$domain"
    if [ -f "$live_dir/fullchain.pem" ]; then
        echo "==> Certificado já existe para $domain, pulando dummy cert."
        continue
    fi
    echo "==> Gerando certificado dummy para $domain..."
    make_dummy "$domain"
done

echo "==> Subindo nginx com os certificados dummy..."
docker compose up -d nginx

failed=""
for domain in $DOMAINS; do
    live_dir="$CONF_DIR/live/$domain"
    archive_dir="$CONF_DIR/archive/$domain"
    renewal_conf="$CONF_DIR/renewal/$domain.conf"
    if [ -f "$renewal_conf" ]; then
        echo "==> Certificado real já emitido para $domain, pulando."
        continue
    fi
    echo "==> Removendo dummy cert e solicitando certificado real para $domain..."
    rm -rf "$live_dir" "$archive_dir" "$renewal_conf"
    if is_apex "$domain"; then
        cert_domains="-d $domain -d www.$domain"
    else
        cert_domains="-d $domain"
    fi
    if docker compose --profile certbot run --rm certbot certonly \
        --webroot -w /var/www/certbot \
        --email "$CERTBOT_EMAIL" \
        $cert_domains \
        --agree-tos --no-eff-email --non-interactive; then
        echo "==> Certificado real emitido para $domain."
    else
        echo "==> FALHA ao emitir certificado para $domain (DNS/firewall pendente?). Restaurando dummy cert para manter o nginx no ar."
        make_dummy "$domain"
        failed="$failed $domain"
    fi
done

echo "==> Recarregando nginx..."
docker compose exec nginx nginx -s reload

if [ -n "$failed" ]; then
    echo "==> Concluído com pendências. Domínios sem certificado real:$failed"
    echo "    Verifique DNS/firewall e rode este script novamente para eles."
    exit 1
fi

echo "==> Concluído. Configure a renovação periódica com ./scripts/renew-certs.sh (ex.: cron)."
