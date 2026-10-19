# VETORLEADS-PROSPECT-10 — auditoria, homologação sintética e publicação preparada

Executado em **19/10/2026 UTC**. O correio mudou em relação à última versão executada (PROSPECT-09), portanto as instruções novas foram executadas dentro dos limites definidos.

- SHA-256 anterior: `c3f86c8fd22b5b95f4bb9f6c22589844fedc7f43b0e6d0cb76892eda3d8b526f`.
- SHA-256 executado: `2ea929dd2c80f3c6f2eaafbb3513abb2d32c3142f76e436d351bb9787d6cbabc`.
- Relatório anterior preservado em [PROSPECT-09](docs/vetorleads-prospect-10/prospect09-executed.md).

**Resultado:** causa dos reinícios identificada; worker/scheduler atualmente ativos, sem novos reinícios durante a auditoria; jornada comercial sintética e interfaces aprovadas; backup atualizado restaurado com sucesso; healthcheck corrigido localmente e override preparado. **A estabilidade de capacidade do host permanece bloqueio para autorizar produção.** Não houve push, deploy, migration operacional, reinício de serviços compartilhados, escrita comercial real ou contato externo.

## Diagnóstico operacional

O kernel registrou **OOM global encerrando `mysqld`** às **10:33:04, 10:35:05 e 10:42:50 UTC**. Os três períodos coincidem com os logs de perda de conexão/`SQLSTATE[HY000] [2002] Connection refused` do worker e scheduler. O MySQL registra três reinícios. No worker, dois registros de parada por perda de conexão e cinco falhas de inicialização observadas explicam os sete reinícios acumulados. Não foi encontrada evidência de coluna ausente, tabela ausente, credencial negada ou falta de comando nessa amostra de logs.

A causa imediata das quedas do banco é falta de memória do host, não incompatibilidade de schema demonstrada. A identidade do consumidor que provocou cada pico não foi determinada. A leitura encontrou aproximadamente **7,8 GiB de RAM, sem swap**, e aproximadamente **3,2 GiB usados em `/tmp` (tmpfs)**. Esses dados demonstram pressão potencial e orientam a revisão de capacidade; não atribuem o incidente exclusivamente ao tmpfs.

`OOMKilled=false` no estado atual do container não exclui os OOM históricos demonstrados pelo kernel. Os eventos Docker antigos não estavam disponíveis na consulta; a evidência histórica usada foi o journal do kernel e a correlação dos logs MySQL/worker.

| Processo | Estado final | Reinícios acumulados | Observação |
| --- | --- | --- | --- |
| App VetorLeads | running / healthy | 0 | PHP-FPM ativo |
| Worker | running / healthy | 7 | PID ativo: `php artisan queue:work database --sleep=3 --tries=3 --timeout=90` |
| Scheduler | running / healthy | 0 | PID ativo: `php artisan schedule:work`; logs atuais sem comandos prontos |
| MySQL compartilhado | running / healthy | 3 | Recuperado após as três quedas por OOM |

A fila estava vazia, **0 jobs falhos e 0 batches**. A leitura operacional inicial/final manteve **48 migrations, 61 prospects, 0 prospects vinculados ao CRM e 0 vendas ganhas**. Datas de inicialização e contadores dos três processos VetorLeads permaneceram iguais durante a etapa. A aplicação operacional foi consultada por PDO em transação READ ONLY, sem bootstrap Laravel para as contagens.

Evidências: [causa MySQL](docs/vetorleads-prospect-10/mysql-restart-cause.json), [cronologia](docs/vetorleads-prospect-10/database-connection-timeline.json), [filas](docs/vetorleads-prospect-10/queues-read-only.json), [serviços iniciais](docs/vetorleads-prospect-10/operational-services.json), [estado final](docs/vetorleads-prospect-10/operational-final-state.json) e [schema/contagens finais](docs/vetorleads-prospect-10/runtime-final-read-only.json). Logs brutos e dados comerciais ficaram fora do Git.

## Correção local do monitoramento

O healthcheck herdado pelos consumidores executa `php-fpm -t`; isso valida configuração FPM e não comprova que `queue:work`/`schedule:work` estão vivos ou que o banco responde. Essa limitação foi corrigida **somente nos artefatos locais**:

- [Probe](scripts/vetorleads-process-health.php): confere os argumentos reais do PID 1, aceita apenas o processo esperado e testa `SELECT 1` numa transação MySQL READ ONLY; captura falhas sem expor credenciais. Não inicializa Laravel, não processa fila, não publica evento e não escreve registros.
- [Override](docs/vetorleads-prospect-10/publication.override.yml): mesma imagem candidata para os três processos; mount somente leitura do probe e healthchecks específicos para worker/scheduler. Compose validado, sem aplicação operacional.
- O probe teve **8 casos de teste aprovados**. Na integração com a imagem candidata e MySQL isolado, **ambos os processos** retornaram exit 0 com banco disponível, exit 1 com endpoint de banco indisponível e exit 1 para processo diferente. [Resultado integrado](docs/vetorleads-prospect-10/health-integration.json).

A correção detecta indisponibilidade e processo errado; não demonstra progresso contínuo da fila nem resolve OOM. Docker não reinicia automaticamente um container apenas por estar unhealthy. A mitigação de capacidade exige decisão e autorização sobre o host compartilhado. Não foi necessário reconstruir funcionalidades comerciais nem alterar regras da aplicação; o VetorLeads permaneceu no commit local `00282e4` com árvore limpa.

## Interfaces e fluxo comercial

Revisão em Chromium com **um tenant, um operador, um produto e uma empresa totalmente sintéticos**, em base separada. O navegador e a aplicação de teste não tiveram rede externa nem acesso à rede operacional. Não houve captura de empresas reais no Google Maps; a entrada autenticada foi exercitada com payload sintético do contrato da extensão. Os parsers/transporte da extensão já haviam passado nos 18 testes PROSPECT-09; não foram declarados como executados novamente aqui.

| Funcionalidade | Verificação nesta etapa |
| --- | --- |
| Página exclusiva de captura | `/prospecting`, título Capturar empresas e navegação próprios |
| Configurações separadas | `/settings/integrations/ab-prospect`, independente da captura |
| Segmento, localização e site | Filtros de categoria, cidade, UF e presença/ausência de site; inclusão e exclusão de resultados corretas |
| Telefone/celular/possível WhatsApp | Identificações mantêm os qualificadores informado/provável/possível; não pressupõem WhatsApp ativo para todo telefone |
| Capturados e duplicados | Primeiro POST 201, recaptura 200; apenas um prospect persistido; histórico/lote visíveis |
| Produto e qualificação | Critérios desejados/excluídos, preferência de site, score v3 e prévia/confirmação individual funcionais |
| Conversão CRM | Um lead vinculado ao prospect; regressão comprova idempotência e preservação de dados |
| Oportunidade/funil | Status `meeting` persistido; rótulo depende do funil configurado (na tela exibida, Em negociação); quadro de oportunidades revisado em desktop/celular |
| Histórico | Três atividades: origem da conversão, movimentação de status e nota explícita de homologação, sem registrar contato externo realizado |
| Dashboard | Indicadores derivados da base sintética; inclusão no CRM/movimentação não tratadas como venda ganha |

A jornada no navegador passou em **1440/390/320 px**, sem erro JavaScript ou transbordamento horizontal do documento na listagem/prévia. A tabela e o quadro usam rolagem interna. O quadro de oportunidades também passou em **1440/390 px**. As capturas foram inspecionadas visualmente. [Resultado navegador](docs/vetorleads-prospect-10/interface/results.json), [captura](docs/vetorleads-prospect-10/interface/1440-capture.png), [filtros](docs/vetorleads-prospect-10/interface/1440-filters.png), [celular](docs/vetorleads-prospect-10/interface/320-leads.png), [funil](docs/vetorleads-prospect-10/interface/1440-board.png) e [dashboard](docs/vetorleads-prospect-10/interface/1440-dashboard.png).

Estado final sintético: **1 tenant, 1 usuário, 1 produto, 1 prospect, 1 lead em `meeting`, 3 atividades, 0 jobs e 0 jobs falhos**. [Contagens](docs/vetorleads-prospect-10/synthetic-final-state.json). Nenhum desses números foi confundido com os dados operacionais.

## Testes realizados

- **1.270 testes aprovados / 13.492 assertions** na regressão completa PHP 8.4/SQLite, com o cenário comercial ampliado na cópia descartável: após conversão, movimentação no funil, nota, histórico e dashboard sem venda fictícia. O incremento reproduzível está em [commercial-acceptance.patch](docs/vetorleads-prospect-10/commercial-acceptance.patch); não altera as funcionalidades do produto.
- **8 testes do probe** aprovados; worker e scheduler integrados à mesma imagem candidata e MySQL **8.4.11**, com usuário restrito a SELECT na cópia restaurada.
- **Chromium:** jornada completa, quadro de oportunidades e revisão responsiva aprovados.
- **Backup atualizado:** restauradas **41 tabelas**, **48 migrations**, **61 prospects**, **2 conversas**, **0 jobs** e **0 jobs falhos**, num banco exclusivo do clone. [Restauração](docs/vetorleads-prospect-10/updated-backup-restore.txt).
- **Compose:** override novo validado sem up/deploy. `git diff --check` aprovado.

A suíte MySQL completa (1.270 testes/13.475 assertions) e o ensaio de migrations foram aprovados na etapa PROSPECT-09; não foram repetidos integralmente nesta etapa, pois nenhuma migration ou regra comercial mudou. O MySQL foi usado novamente para restaurar o backup atual e integrar os dois consumidores. Os ajustes do harness do navegador trataram o formato Inertia 3 e os redirecionamentos dos formulários; não corresponderam a correções da aplicação.

## Imagens, commits e backup

Candidato reutilizado, sem rebuild comercial: `vetorleads:prospect09-00282e4`, ID **`sha256:4d9deab99bb7ab4d2ffbe660983cb668ca34a931ae34cd4e982d957d8181a733`**, commit **`00282e4ab046b4fc0f7bd0a5f33766255104df3e`**. Worker e scheduler iniciaram no ensaio com essa imagem; o app foi revisado em PHP 8.4 pela cópia de suas fontes e dos assets extraídos da candidata. A compatibilidade não depende de manter as três imagens operacionais distintas.

IDs operacionais preservados:

| Processo | Imagem atual / recuperação local |
| --- | --- |
| App | `sha256:fba2f79c606c94196015432706c772ec07c296d35f84d2c975836c8253c50c52` / `vetorleads:prospect10-recovery-vetorleads` |
| Worker | `sha256:8f836c54ab0c797ba820bc0233a2f9fa85f5a25b3e705b0c4e32548e4094b3b8` / `vetorleads:prospect10-recovery-vetorleads-worker` |
| Scheduler | `sha256:669822b906e6bc5c8258f37b0f4085b57d0a158b36f0c21a1c034d80293cbc30` / `vetorleads:prospect10-recovery-vetorleads-scheduler` |

Os arquivos críticos amostrados e o `composer.lock` têm os mesmos hashes entre os três consumidores operacionais e os arquivos locais conferidos; isso não constitui comparação integral de cada imagem. A nova publicação apenas propõe fixar um único artefato verificado e o probe.

Remotos conferidos por `git ls-remote`, sem fetch/pull/push: infraestrutura `main` em `1165aa9bd35d8fa455ab100340d7cf1e20f78594`; VetorLeads `main` em `adcc6e40f8025161882c95631f29cb69eab0bdba`, com candidato local um commit à frente. Há alterações locais de documentação, `.gitignore`, probe e ponteiros para revisão. A mudança preexistente do VetorOS para `953b90a825c76239055c29f90d77530770e6c2a5` foi preservada e não foi publicada nem adicionada a commit nesta etapa. [Inventário](docs/vetorleads-prospect-10/repositories.json).

O snapshot atualizado de **469.702 bytes**, assets servidos e logs privados estão em `staging/prospect10/`, ignorados pelo Git. Snapshot SHA-256: `81b395558c083b904d83f4e9e884172eb791062483a972efea8bc2ba75297391`. Manifest servido SHA-256: `257b66d4360420d17e961c10dcc4ad5aabb604127cdd1b10c93b44708576e37b`. [Metadados](docs/vetorleads-prospect-10/backup-metadata.json). O backup foi restaurado em clone com memória limitada, sem sobrepor banco operacional; nenhum volume de assets servido foi escrito.

## Procedimento e autorizações pendentes

O [roteiro de publicação, recuperação e homologação](docs/vetorleads-prospect-10/publicacao-recuperacao-homologacao.md) contém comandos delimitados aos três serviços, IDs/tags individuais de recuperação, cópia dos assets e condições de interrupção. Reverter imagem/configuração preserva as colunas aditivas; **não usar rollback destrutivo de migrations nem restaurar snapshot antigo sobre produção**. Reparação comercial, se necessária, será seletiva e autorizada por tenant/ID.

A homologação real está preparada para um tenant/operador e até três empresas previamente autorizadas. Antes de qualquer escrita, deve comprovar que gatilhos, filas, WAHA e n8n não enviarão mensagens/eventos externos. Fila vazia na leitura não garante ausência de automações futuras. Tenant, operador, produto e empresas reais não foram escolhidos nesta etapa.

Exigem autorização expressa:

1. Mitigação de capacidade do host compartilhado e validação de estabilidade após a mudança. **Este é o bloqueio técnico atual para produção**; o healthcheck apenas detecta a indisponibilidade.
2. Commit/push dos arquivos de infraestrutura e ponteiro VetorLeads revisados, excluindo a alteração preexistente do VetorOS.
3. Janela e aplicação do override exclusivamente em app/worker/scheduler VetorLeads, sem rebuild ou reinício de serviços compartilhados.
4. Escritas comerciais reais da amostra e qualquer bloqueio operacional de automações necessário para garanti-las sem contatos externos. Mensagens/contatos continuam fora do escopo.
5. Qualquer alteração adicional de schema, host ou recuperação de dados. Não há migration nova pendente neste correio.

**Preparação e homologação sintética concluídas. O critério de estabilidade operacional integral permanece pendente da mitigação e validação de memória; a publicação não está liberada automaticamente.** Os containers de teste foram parados ao terminar; apenas seus dados privados de recuperação foram preservados. Os serviços operacionais não foram reiniciados nem alterados.
