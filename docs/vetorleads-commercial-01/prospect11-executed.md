# VETORLEADS-PROSPECT-11 — Publicação controlada na VPS

Executado em **19/10/2026 UTC**, com observação final às **12:46:52 UTC**.

**Estado final: PUBLICADO COM RESSALVAS.** App, worker e scheduler publicados e saudáveis, com a mesma imagem verificada. MySQL estável na janela observada, dados comerciais preservados e demais containers inalterados. A jornada autenticada em produção com registros reais permanece pendente; a revisão funcional completa utilizou dados sintéticos em ambiente isolado. A estabilidade observada após o reboot não comprova capacidade sob picos futuros.

## Comparação do correio

O `correio.md` mudou de PROSPECT-10 para PROSPECT-11, passando a autorizar publicação condicionada às verificações operacionais. A comparação utilizou o hash do último correio executado registrado no relatório anterior; o HEAD Git ainda continha PROSPECT-08 e não era a última instrução efetivamente executada.

- SHA-256 anterior: `2ea929dd2c80f3c6f2eaafbb3513abb2d32c3142f76e436d351bb9787d6cbabc`.
- SHA-256 executado: `3a69a95cc535bfbb25e7f310471426fb7790cda78596eefbaff83e2483230283`.
- [Relatório anterior preservado](docs/vetorleads-prospect-11/prospect10-executed.md) e [identificação da execução](docs/vetorleads-prospect-11/execution.json).

## Memória e estabilidade após reinicialização

Na primeira leitura, o host tinha cerca de 3 minutos de uptime, **7,8 GiB de RAM**, **4,8 GiB disponíveis**, aproximadamente **2,9 GiB usados** e **nenhum swap**. `/tmp` ocupava aproximadamente 28 KiB. Na etapa anterior, havia aproximadamente 3,2 GiB em `/tmp` e três OOM globais encerrando o MySQL.

Na leitura final, com 15 minutos de uptime e cerca de 7 minutos após a publicação, havia **5.212.553.216 bytes disponíveis (4,85 GiB)** e **3.119.271.936 bytes usados (2,91 GiB)**. `/tmp` ocupava 20 KiB; `/run`, 2,4 MiB. A raiz tinha **57 GiB livres**, com 41% ocupado. Não foi encontrado evento OOM no journal do kernel do boot atual. Não houve limpeza de `/tmp`, criação de swap nem alteração de limites ou serviços compartilhados nesta execução.

O MySQL **8.4.11** iniciou às 12:31:05 UTC, concluiu a recuperação XA e ficou pronto às 12:31:16, sem novos reinícios. Os três reinícios do worker antigo ocorreram na subida inicial, com erros de conexão recusada enquanto o banco inicializava; seu contador permaneceu estável antes da troca. Os novos consumidores terminaram com zero reinícios. Os logs pós-publicação não apresentaram SQLSTATE, fatal, exception ou erro de produção na amostra examinada; também não foram encontrados ERROR/CRITICAL/EMERGENCY posteriores à publicação no log Laravel.

Evidências: [memória antes](docs/vetorleads-prospect-11/memory-before-publication.json), [memória final](docs/vetorleads-prospect-11/memory-final.json), [serviços anteriores](docs/vetorleads-prospect-11/services-before.json), [serviços finais](docs/vetorleads-prospect-11/services-final.json), [logs dos processos](docs/vetorleads-prospect-11/logs-summary.json) e [log Laravel](docs/vetorleads-prospect-11/application-log-check.json). Logs brutos ficaram no diretório privado de backup.

## Imagens, configuração e commits

- Aplicação: commit **`00282e4ab046b4fc0f7bd0a5f33766255104df3e`**, árvore Git limpa, reutilizado sem rebuild.
- Infraestrutura: commit local **`3bd412a1f1673da58afcd7af4088d17f94f58c77`**, contendo somente `.gitignore`, ponteiro VetorLeads, override e probe.
- Imagem dos três processos: **`vetorleads:prospect09-00282e4`**.
- ID verificado: **`sha256:4d9deab99bb7ab4d2ffbe660983cb668ca34a931ae34cd4e982d957d8181a733`**; o RepoDigest local informado pelo Docker corresponde ao mesmo SHA-256. [Conferência](docs/vetorleads-prospect-11/candidate.json).

O [override persistente](docker-compose.override.yml) foi validado com o Compose base. Os consumidores foram parados para a troca, e a publicação usou `up -d --no-deps --no-build --pull never`, delimitada a `vetorleads`, `vetorleads-worker` e `vetorleads-scheduler`. Nenhuma migration foi executada. O app manteve o healthcheck PHP-FPM; worker e scheduler receberam o [probe específico](scripts/vetorleads-process-health.php), montado somente para leitura, que confere o processo e a conexão MySQL por SELECT em transação READ ONLY.

Os três processos ficaram `healthy`, na mesma imagem e com zero reinícios. Os oito casos do probe passaram antes da publicação; os probes dos dois consumidores também passaram dentro dos containers publicados. A fila e os jobs falhos permaneceram zerados. Isso comprova processo/conexão, sem disparar tarefas comerciais para demonstrar consumo.

A alteração preexistente do ponteiro VetorOS foi excluída do commit e preservada na árvore de trabalho. Não houve deploy do VetorOS, push ou reinício de MySQL, Redis, Nginx, WAHA, n8n ou outros produtos. IDs, datas de início e contadores de reinício dos demais containers coincidiram com a leitura anterior à publicação.

## Backup e recuperação

Backup novo via `mysqldump --single-transaction --quick`, limitado ao banco VetorLeads, após conferir que as tabelas eram InnoDB. Inclui routines, events, triggers e dados binários. Armazenamento privado: **`backups/prospect11/`**, fora do Git e com diretório de acesso restrito.

- Snapshot: **471.080 bytes**, SHA-256 **`b0f803522ebda0df6a4c53a080c2562ee7e834f118f9cdca90040c8430c5df9a`**.
- Restaurado em MySQL 8.4 isolado, sem rede e limitado a 768 MiB: **41 tabelas**, contagens integralmente iguais e `CHECK TABLE ... QUICK` aprovado em todas.
- Assets servidos preservados em `backups/prospect11/assets/`; SHA-256 do manifest: **`257b66d4360420d17e961c10dcc4ad5aabb604127cdd1b10c93b44708576e37b`**.
- [Metadados](docs/vetorleads-prospect-11/backup.json) e [resultado da restauração](docs/vetorleads-prospect-11/restore.json).

| Processo | Imagem anterior | Tag de recuperação |
| --- | --- | --- |
| App | `sha256:fba2f79c606c94196015432706c772ec07c296d35f84d2c975836c8253c50c52` | `vetorleads:prospect11-recovery-vetorleads` |
| Worker | `sha256:8f836c54ab0c797ba820bc0233a2f9fa85f5a25b3e705b0c4e32548e4094b3b8` | `vetorleads:prospect11-recovery-vetorleads-worker` |
| Scheduler | `sha256:669822b906e6bc5c8258f37b0f4085b57d0a158b36f0c21a1c034d80293cbc30` | `vetorleads:prospect11-recovery-vetorleads-scheduler` |

O override de recuperação foi salvo em `backups/prospect11/recovery.json`. Pode ser aplicado com o Compose base e esse arquivo explicitamente, delimitado aos três serviços, sem dependências e sem rebuild. A cópia preservada permite recuperar os assets. Não foi necessário executar recuperação operacional. O teste de restauração não sobrepôs produção; nenhuma coluna, tabela ou dado comercial foi removido. Os containers isolados foram parados ao concluir os testes.

## Homologação e preservação dos dados

**Produção:** acesso HTTPS pelo Nginx local, usando o domínio real e validação de certificado: landing e login **200**; dashboard, captura, configurações, prospects, produtos e CRM **302**, exigindo autenticação. CSS e JavaScript de entrada do manifest responderam **200**. O teste foi feito pela própria VPS, sem comprovar acesso a partir de uma rede externa. [Resultados HTTP](docs/vetorleads-prospect-11/http.json).

**Regressão isolada:** **1.270 testes aprovados, 13.492 assertions, 48,96 segundos**, em PHP 8.4/SQLite, com fixtures sintéticas e sem rede externa. Abrange isolamento de tenants, filtros, score, critérios por produto, conversão, deduplicação, histórico e dashboard. [Log](docs/vetorleads-prospect-11/functional-tests.log).

**Navegador isolado:** login com operador sintético e revisão de dashboard, captura exclusiva, configuração da extensão, prospects, produtos, CRM e funil em **1440 e 390 px**. Todas as páginas responderam 200, sem erros JavaScript e sem transbordamento horizontal do documento. A região específica do funil foi confirmada. Capturas foram geradas; amostras desktop/celular foram inspecionadas visualmente. [Resultado](docs/vetorleads-prospect-11/interface/browser.json), [prospects no celular](docs/vetorleads-prospect-11/interface/390-_prospecting_prospects.png) e [funil desktop](docs/vetorleads-prospect-11/interface/1440-_leads_tab_kanban.png).

As fontes usadas nesses testes foram comparadas com a imagem publicada: **404 arquivos**, incluindo app, config, routes, resources, migrations e lockfiles, sem diferenças. [Paridade](docs/vetorleads-prospect-11/source-parity.json). Os ajustes necessários ao ambiente visual recuperaram o navegador perdido no reboot e corrigiram o roteamento de assets do servidor PHP de teste; não alteraram o produto.

As contagens comerciais antes/depois permaneceram iguais: **48 migrations, 61 prospects, 61 qualificações, 1 lead, 4 atividades, 2 conversas, 0 jobs e 0 jobs falhos**. Também foi comparada a lista nominal das 48 migrations. Sessões/cache técnicos não foram usados como critério de igualdade comercial. [Antes](docs/vetorleads-prospect-11/counts-before.json), [depois](docs/vetorleads-prospect-11/counts-after.json) e [migrations](docs/vetorleads-prospect-11/migrations-after.json).

## Ressalvas e pendências

1. Não houve login com identidade real nem alteração comercial em produção. Conversão, associação de produto, qualificação persistente e histórico foram validados pela regressão sintética; a inspeção visual utilizou a base sintética já existente. A homologação real depende de tenant, operador e registros previamente autorizados.
2. A ausência de OOM e a folga atual aprovaram a janela, mas o host continua sem swap. A observação curta não substitui acompanhamento sob carga. Não foi aplicada mitigação global de capacidade.
3. O probe verifica processo e conectividade, não progresso de fila; Docker não reinicia automaticamente um processo apenas por ficar unhealthy.
4. Commits locais foram preparados, sem push. Evidências anteriores PROSPECT-09/10 e a alteração preexistente VetorOS foram preservadas. Nenhuma mensagem real, contato externo ou automação comercial foi acionada pelos testes.

**Publicação operacional concluída com ressalvas de homologação real e observação de capacidade.** Evidências e scripts desta execução estão em `docs/vetorleads-prospect-11/`; backup, credenciais de clone e logs privados permanecem fora do Git.
