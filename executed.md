# Execução — VETORLEADS-DEPLOY-01

Auditoria e homologação feitas em 20/10/2026 (UTC). **A implantação já estava aplicada** quando esta execução começou: os containers do VetorLeads rodavam o commit `809e347` desde 19:23–19:24 UTC, com backup e migrations feitos. Por isso não reconstruí nem reiniciei serviços. Esta execução só leu dados; as escritas foram em ambientes descartáveis fora da produção.

## Comparação do correio

O `correio.md` mudou de **VETORLEADS-PROSPECT** (implementação) para **VETORLEADS-DEPLOY-01** (implantação e homologação). SHA-256 atual: `376ed2bb59d49c1d2767f7e4a488ea80fd6f13792fc89ebd1fd1b7ef1bd4d0d3`. A instrução está preservada em [correio.md](docs/vetorleads-deploy-01/correio.md), e o relatório anterior foi arquivado em [previous-executed.md](docs/vetorleads-deploy-01/previous-executed.md) (SHA-256 `5a372885247f672269bb181079e1115fc0b0ff3d0a420bf939fd371a152f6640`).

## 1. Auditoria

- **Versão implantada**: VetorLeads `809e347` (merge de `origin/main`), na imagem `vetorleads:pull-809e347` criada em 2026-10-20 19:23:44 UTC. É a mesma imagem em `vetorleads`, `vetorleads-worker` e `vetorleads-scheduler`. O working tree do submódulo está limpo.
- **Implementações presentes no código**:
  - VETORLEADS-06 (`ca30788`) faz parte do histórico de `HEAD`.
  - UX-04 e PROSPECT-05 não aparecem em mensagens de commit, mas as evidências em `docs/vetorleads-ux-04` e `docs/vetorleads-prospect-05` são anteriores a PROSPECT-08 e PROSPECT-09, que estão em `HEAD`. A suíte atual também é maior: 1.373 testes, contra 1.256 em PROSPECT-05.
  - Também estão no código as correções posteriores: PROSPECT-08 e 09, ciclo comercial e propostas, WebChat piloto e análise por lote (VETORLEADS-PROSPECT).
- **Superprojeto `infra-abrasil`**: há alterações locais não commitadas, que foram preservadas:
  - ponteiros dos submódulos `vetorleads`, `vetoros`, `vetorpet` e `desgarrados`;
  - `docker-compose.override.yml`, que fixa a imagem em `pull-809e347` (antes `chat130-859241bd9bbc`).
- **Containers**: os 21 containers estão `healthy`. Os outros sistemas (VetorOS, VetorPet, Desgarrados, abrasilsistema, WAHA, n8n, Redis, MySQL, phpMyAdmin) não foram tocados.
- **Nginx**: `nginx -t` passou.
- **Migrations**: não há nenhuma pendente. A última aplicada é `2026_10_20_170000_create_prospect_analysis_batches`.
- **Banco MariaDB/MySQL**: o serviço `mysql:8.4` está operacional e o banco `vetorleads` tem 59 tabelas.
- **Ambiente**:
  - `APP_ENV=production`, `APP_DEBUG=false`, `QUEUE_CONNECTION=database`.
  - `SUPPORT_AUTOMATION_WHATSAPP_DELIVERY_ENABLED=false`, então não há envio automático de WhatsApp.
- **Integrações**:
  - WAHA está configurado (URL e chave presentes, `tenant_enabled=true`) e responde `pong`.
  - O n8n responde `healthz` ok, mas o webhook de envio do n8n (`N8N_WHATSAPP_WEBHOOK_URL`) **não está configurado** no VetorLeads.
  - O WebChat está configurado para o tenant 1.
- **Flag comercial**: `SAAS_COMMERCIAL_ENABLED=true` (no `.env` raiz como `VETORLEADS_SAAS_COMMERCIAL_ENABLED` e no `.env` do app), então a área comercial já está habilitada. Não alterei a flag.
- **Build frontend**: os assets estão embutidos na imagem e servidos; as páginas carregam.

Não encontrei nenhum bloqueio que pusesse dados ou serviços em risco.

## 2. Backup e segurança

- O backup pré-migração existe: `backups/vetorleads_pre_migrate_20261020_192431.sql` (525 KB, dump completo, 50 tabelas).
- **Restauração testada** num MySQL 8.4 descartável (`vl-restore-test`, removido em seguida): importou sem erros, com 50 tabelas, 61 prospects, 2 usuários e 1 tenant, os mesmos números da produção.
- Backups anteriores ficam em `backups/` (18/10).
- **Incidente do relatório anterior** (um `migrate:fresh` acidental): a produção **não foi afetada**. O banco `vetorleads` do container `mysql` mantém 61 prospects reais (o mais antigo de 18/10) e não tem o usuário sintético `demo@example.test`.
- Nenhuma credencial foi copiada para este relatório.

## 3. Implantação

Nenhuma ação foi necessária, porque o deploy já estava aplicado:

- imagem nova nos três serviços com `--no-deps`;
- backup;
- `migrate --force`.

Não rodei `docker compose down`, não recriei volumes e não mudei configuração operacional. Cache de config e de rotas não está ativo (`config_cached=false`), como no padrão anterior da imagem. Não ativei porque mudaria o comportamento em produção sem necessidade. As permissões de `storage` e `bootstrap/cache` são `www-data` e estão corretas.

## 4. Homologação funcional

**Somente leitura, em produção** (HTTPS público):

| Rota | Resultado |
|---|---|
| `/` (landing), `/login`, `/register` | 200 |
| `/politica-de-privacidade`, `/politica-de-cookies`, `/termos-de-uso` | 200 |
| `/up` (health) | 200 |
| Áreas autenticadas: `/dashboard`, `/prospecting`, `/prospecting/prospects`, `/prospecting/searches`, `/leads`, `/settings/pipeline`, `/settings/integrations/ab-prospect`, `/commercial/reports`, `/proposals`, `/web-chat`, `/area-restrita` | 302 → `/login` (protegidas) |
| Download da extensão `files/ab-prospect-firefox-v1.4.0.xpi` | 200, 29.504 B |
| `/sitemap.xml` | **500** (ver Problemas) |

**Fluxos autenticados (itens 1–13)**: rodei a suíte automatizada do commit implantado (`809e347`) numa cópia no scratchpad, com SQLite isolado e HTTP externo bloqueado. Assim não houve escrita em produção.

- Resultado: **1.372 aprovados, 1 falha, 14.285 assertions**.
- A única falha é `BlogTest`, porque a imagem `composer:2` usada no teste não tem GD. É uma limitação conhecida do ambiente de teste; a imagem de produção tem GD.
- A suíte cobre cadastro e login, isolamento entre tenants, dashboard, captura, leads e filtros, qualificação, conversão explícita ao CRM com prévia, edição de leads, funil Kanban, follow-ups, valores e negócios ganhos, integração AB Prospect e páginas legais.

Não fiz login com contas reais nem criei dados sintéticos em produção. Por isso, a navegação autenticada no ambiente publicado continua **pendente de verificação manual** pelo operador.

**Fila**: o worker processa normalmente e `failed_jobs` está em 0. O lote de análise id 2 (`stale_only`, iniciado pelo usuário 1 às 19:31 UTC) está em `running`, com 23 itens concluídos e 2 pendentes aguardando retry agendado. Não interferi.

## 5. Prospects reais (somente leitura)

São 61 prospects, todos do tenant 1.

| Indicador | Quantidade |
|---|---|
| Com telefone (fixo, celular ou WhatsApp) | 61 |
| Com localização (lat/lng) | 60 |
| Com endereço | 41 |
| Com cidade preenchida | 3 |
| Com website | 45 |
| Com CNPJ | 3 |
| Com e-mail | 2 |
| Convertidos em lead | 0 |

- **Qualificação**: os 61 prospects têm registro. 25 estão na regra atual **v4**, que sucede os critérios v3 (22 alta, 3 muito alta). Outros 36 continuam em **v2** (34 baixa, 2 média). Nenhum prospect está em v3, porque o código atual usa `RULES_VERSION = v4`.
- **Situação cadastral**: 36 sem validação, 21 indeterminada, 3 provavelmente ativa e 1 encerrada.

Não fiz requalificação em massa.

## Problemas encontrados

1. **`/sitemap.xml` retorna 500**. É anterior a este deploy. A imagem PHP tem `short_open_tag=On`, então o `<?xml ...?>` de `resources/views/sitemap.blade.php` é interpretado como PHP (`syntax error, unexpected identifier "version"`). Para corrigir, basta emitir a declaração como string no Blade (`{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}`) ou desligar `short_open_tag`. Não corrigi porque esta execução não admite alterações de código.
2. O campo `city` está vazio em 58 dos 61 prospects, e o CNPJ só existe em 3. Isso limita a validação cadastral e a deduplicação por município.
3. 36 prospects continuam com qualificação v2 (desatualizada).
4. O webhook de envio do n8n não está configurado no VetorLeads, então o envio manual de WhatsApp via n8n fica indisponível. O envio automático está desligado de propósito.

## Pendências para liberação comercial

- Corrigir o `sitemap.xml` (SEO) num próximo ciclo de código.
- Verificar manualmente os fluxos autenticados com uma conta real: dashboard, captura, conversão ao CRM, Kanban e follow-ups.
- Requalificar para v4 os 36 prospects em v2, **mediante autorização** (por exemplo, "Reanalisar apenas desatualizados" no lote).
- Configurar `N8N_WHATSAPP_WEBHOOK_URL`, se o envio manual via n8n fizer parte da oferta.
- Commitar no superprojeto os ponteiros de submódulo e o `docker-compose.override.yml` (`pull-809e347`), para o estado versionado corresponder ao que está implantado.
- A flag `SAAS_COMMERCIAL_ENABLED` já está `true`. Confirmar que a habilitação foi autorizada.

Nenhuma funcionalidade nova foi adicionada, nenhuma mensagem foi enviada e nenhum serviço foi reiniciado.
