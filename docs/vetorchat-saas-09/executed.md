# Execução — VETORCHAT-SAAS-09

Data: 22/10/2026, ~17:25–17:30 (BRT).

**Estado: VetorLeads publicado na VPS no commit `853e2c4`.** Nenhum outro produto foi alterado, nenhum outro container foi recriado e nenhum volume foi removido.

## Escopo

- **Publicado:** VetorLeads (`vetorleads`, `vetorleads-worker`, `vetorleads-scheduler`) no commit `853e2c4`, a partir de `5f018d9`.
- **Desgarrados:** mantido no commit `dad5134`. O gitlink do superprojeto (`f87a15e`) não existe em `brasilgb/desgarrados3`, porque os históricos são independentes. Não houve merge, reset, checkout nem alteração de container. A correção desse histórico fica fora desta etapa.
- **Sem alteração:** ABrasil Sistemas, VetorOS, VetorPet, MySQL, Redis, WAHA, n8n e phpMyAdmin.

## Pré-checagens

- **Compatibilidade:** o diff `5f018d9..853e2c4` é aditivo. Ele traz o widget público (`public/widget.js`), produtos de chat e configurações, mais duas migrations novas:
  - `2026_10_21_140000_create_tenant_chat_widgets`: tabela nova e colunas `nullable` em `web_conversations`;
  - `2026_10_21_150000_create_chat_products`: tabelas novas, colunas `nullable` e unique com coluna `nullable`. O `down()` lança exceção de propósito (rollback não destrutivo).

  A imagem anterior funciona sobre o schema novo.
- **Variáveis:** `VETORCHAT_SCRIPT_URL` e `VETORCHAT_WIDGET_KEY` já estão definidas no Compose (a chave fica vazia no VetorLeads; ela é usada pelo site cliente).
- **Compose:** o `docker-compose.override.yml` recebido no pull fixava o VetorLeads em `pull-549aba8` (regressão). Ele foi refixado nos três serviços para a tag nova `vetorleads:vetorchat09-853e2c4`, o que preserva a imagem anterior.
- **Integridade antes:** todos os containers estavam `healthy` e `migrate:status` não tinha nada pendente na versão em execução.

## Backup

- Arquivo: `backups/vetorleads_pre_vetorchat09_20261022_172530.sql` (1,18 MB).
- SHA-256: `938f5027887f9880940cd572e61ebdff8bc78805e71e455491654861e25b490d` (também em `.sha256` ao lado).
- **Restauração testada** num container `mysql:8.4` temporário e isolado (`--network none`): import sem erros, 54/54 tabelas e contagens iguais à produção (`users` 2, `tenants` 1, `web_conversations` 4, `migrations` 57).

## Execução

1. `docker compose build vetorleads vetorleads-worker vetorleads-scheduler`: OK, imagem `4c716db9788d`.
2. `docker compose run --rm --no-deps vetorleads php artisan migrate --force`: as duas migrations rodaram com `DONE`.
3. `docker compose up -d --no-deps vetorleads vetorleads-worker vetorleads-scheduler`: os três ficaram `healthy`.
4. `nginx -t` OK e `nginx -s reload`.

## Verificação pós-publicação

| Verificação | Resultado |
|---|---|
| `https://vetorleads.com.br/widget.js` | 200 `application/javascript`, 19.337 B; SHA igual ao `public/widget.js` do commit |
| `https://vetorleads.com.br/up` | 200 |
| `/` e `/login` | 200 |
| Logs dos três serviços (5 min) e `laravel.log` | sem ERROR/CRITICAL/exception |
| Demais containers | inalterados e `healthy` (uptime preservado) |

## Rollback

1. Primeira opção (sem tocar no banco): voltar as três linhas `image:` do VetorLeads no override para `vetorleads:pull-5f018d9`, rodar `docker compose up -d --no-deps vetorleads vetorleads-worker vetorleads-scheduler` e recarregar o nginx. O schema novo é compatível com a versão anterior.
2. Último recurso: restaurar o dump acima. Isso perde os dados gravados depois de 17:25.

## Pendências observadas (não tratadas nesta etapa)

- O override recebido fixa o **VetorOS** em `vetoros:chat129-1700256f53fd`, imagem que **não existe na VPS**. Os containers seguem em `pull-370f08f1`, mas qualquer recriação do VetorOS falharia. É preciso corrigir o pin antes de mexer no VetorOS.
- Gitlink do Desgarrados (`f87a15e`) sem correspondência no remoto, conforme descrito acima.
- A alteração no `docker-compose.override.yml` e este relatório não foram commitados.
