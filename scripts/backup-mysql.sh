#!/bin/sh
set -eu
# Backup diário do MySQL: um dump compactado por banco, com checksum e retenção.
# Cron (host): 30 2 * * * /opt/infra-abrasil/scripts/backup-mysql.sh >> /var/log/mysql-backup.log 2>&1
#
# Os arquivos ficam em backups/daily/<AAAAMMDD>/ (fora do git). Um dump só é aceito se
# terminar com a linha "Dump completed"; se algum banco falhar, o script sai com erro e
# não apaga backups antigos.
cd "$(dirname "$0")/.."

RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-14}"
DEST="backups/daily/$(date +%Y%m%d)"
mkdir -p "$DEST"

echo "[$(date '+%F %T')] início do backup em $DEST"

# A senha é lida dentro do container (variável MYSQL_ROOT_PASSWORD), sem passar pela linha de comando do host.
DATABASES=$(docker compose exec -T mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot -N -e "SHOW DATABASES"' \
    | grep -Ev '^(information_schema|performance_schema|mysql|sys)$')

[ -n "$DATABASES" ] || { echo "nenhum banco encontrado"; exit 1; }

FAILED=0
for DB in $DATABASES; do
    FILE="$DEST/$DB.sql.gz"
    if docker compose exec -T mysql sh -c "MYSQL_PWD=\"\$MYSQL_ROOT_PASSWORD\" mysqldump -uroot --no-tablespaces --single-transaction --routines --events --triggers \"$DB\"" \
        | gzip > "$FILE.tmp" && zcat "$FILE.tmp" | tail -n 1 | grep -q 'Dump completed'; then
        mv "$FILE.tmp" "$FILE"
        echo "  ok    $DB ($(du -h "$FILE" | cut -f1))"
    else
        rm -f "$FILE.tmp"
        echo "  FALHA $DB"
        FAILED=1
    fi
done

(cd "$DEST" && sha256sum ./*.sql.gz > SHA256SUMS)

if [ "$FAILED" -ne 0 ]; then
    echo "[$(date '+%F %T')] backup com falhas; retenção não aplicada"
    exit 1
fi

# Retenção: remove só pastas diárias (nome com 8 dígitos) mais antigas que o limite.
find backups/daily -mindepth 1 -maxdepth 1 -type d -name '[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]' -mtime "+$RETENTION_DAYS" -exec rm -rf {} +

echo "[$(date '+%F %T')] backup concluído ($(du -sh "$DEST" | cut -f1)); retenção de $RETENTION_DAYS dias"
