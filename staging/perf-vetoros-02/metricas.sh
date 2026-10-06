#!/bin/sh
# PERF-VETOROS-02: métricas por rota do VetorOS a partir do log "timed" do Nginx.
# Uso:   sh staging/perf-vetoros-02/metricas.sh [janela]      (padrão: 2h; formato do `docker compose logs --since`)
# Teste: METRICAS_LOG=arquivo.log sh staging/perf-vetoros-02/metricas.sh
# Reloads parciais de `notifications` (campo pd=) são contados à parte.
# Só lê logs. Linhas no formato antigo ("combined", sem uri=) são ignoradas.
SINCE="${1:-2h}"
cd "$(dirname "$0")/../.." || exit 1

if [ -n "${METRICAS_LOG:-}" ]; then
    cat "$METRICAS_LOG"
else
    docker compose logs --no-log-prefix --since "$SINCE" nginx 2>/dev/null
fi | grep ' host=vetoros.com.br ' | awk '
function pct(src, r, n, p,   a, i, j, t, k) {
    for (i = 1; i <= n; i++) a[i] = src[r, i]
    for (i = 2; i <= n; i++) { t = a[i]; j = i - 1; while (j > 0 && a[j] > t) { a[j + 1] = a[j]; j-- } a[j + 1] = t }
    k = int(n * p + 0.999); if (k < 1) k = 1
    return a[k]
}
{
    # campos "combined": $9 = status, $10 = bytes
    st = $9; bytes = $10; uri = ""; rt = ""; urt = ""; ce = ""; pd = ""
    for (i = 1; i <= NF; i++) {
        if ($i ~ /^uri=/) uri = substr($i, 5)
        else if ($i ~ /^rt=/) rt = substr($i, 4)
        else if ($i ~ /^urt=/) urt = substr($i, 5)
        else if ($i ~ /^ce=/) ce = substr($i, 4)
        else if ($i ~ /^pd=/) pd = substr($i, 4)
    }
    if (uri == "") next
    sub(/\?.*/, "", uri)
    # reload parcial do polling (X-Inertia-Partial-Data: notifications), de qualquer tela, vai numa linha própria
    if (pd == "notifications") r = "polling notif."
    else if (uri == "/app" || uri == "/app/orders" || uri == "/app/schedules" || uri == "/app/messages") r = uri
    else if (uri ~ /^\/app\/orders\/[0-9]+$/) r = "/app/orders/{id}"
    else { if (st >= 500 || st == 499) outros[st]++; next }
    n[r]++; RT[r, n[r]] = rt + 0
    if (urt != "-" && urt != "") { m[r]++; URT[r, m[r]] = urt + 0 }
    B[r] += bytes
    if (ce == "gzip") gz[r]++
    if (st >= 500) e5[r]++
    if (st == 499) e499[r]++
}
END {
    printf "%-18s %6s %8s %8s %8s %8s %11s %6s %4s %4s\n", "rota", "reqs", "rt_p50", "rt_p95", "urt_p50", "urt_p95", "bytes_med", "gzip", "5xx", "499"
    split("/app|/app/orders|/app/orders/{id}|/app/schedules|/app/messages|polling notif.", R, "|")
    for (k = 1; k <= 6; k++) {
        r = R[k]
        if (!n[r]) { printf "%-18s %6d\n", r, 0; continue }
        u50 = u95 = 0
        if (m[r]) { u50 = pct(URT, r, m[r], .5); u95 = pct(URT, r, m[r], .95) }
        printf "%-18s %6d %8.3f %8.3f %8.3f %8.3f %11d %5.0f%% %4d %4d\n", r, n[r], pct(RT, r, n[r], .5), pct(RT, r, n[r], .95), u50, u95, B[r] / n[r], 100 * gz[r] / n[r], e5[r], e499[r]
    }
    for (s in outros) printf "outras rotas VetorOS com status %s: %d\n", s, outros[s]
}'

if [ -z "${METRICAS_LOG:-}" ]; then
    echo
    docker stats --no-stream --format 'table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}' | grep -E 'NAME|vetoros|nginx|mysql'
fi
