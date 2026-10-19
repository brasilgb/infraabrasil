# VETOR-CHAT-12.9 — Laboratório publicado e sandbox homologado na VPS

## Comparação com a solicitação anterior

`correio.md` mudou de CHAT-12.8 para CHAT-12.9. SHA-256 atual: `e127c709c8b10562a24c3c1d206bb40c6f616746389941f9313f35638277e20f`; solicitação anterior: `c63f75d5a0805c3c10e89858adf71559138832cdf2bbadc1e084fc2b5d79c6f8`. A nova etapa autoriza publicação coordenada, backup/ensaio e homologação do sandbox na infraestrutura real. Solicitação atual arquivada em `docs/vetor-chat-12/chat129-correio.md` e `docs/vetor-chat-12/chat129/correio.md`; relatório anterior preservado em `chat129/previous-executed.md`. Os relatórios CHAT-12.7 e CHAT-12.8 também foram consultados.

## Resultado

**Laboratório publicado e jornada sandbox aprovada na VPS. Atendimento automático público não foi ativado.**

O administrador autorizado pode abrir `https://vetorleads.com.br/settings/support-automation/lab`, selecionar regras inativas e emitir o código temporário para o widget de `https://vetoros.com.br`. A homologação assistida no navegador conferiu os 14 passos, incluindo contexto, prévia de oportunidade, operador, retomada do automático, encerramento, revogação e expiração. O navegador registrou **zero chamadas a endpoints operacionais e zero erros JavaScript**. Seleção de regra de outro tenant retornou **422**; código expirado/usado também retornou **422**. Após expiração/revogação e recarga, o widget continuou bloqueado no modo de homologação, sem fallback.

As 14 regras operacionais continuam inativas e sem alterações; WhatsApp automático global e por tenant continuam desligados. Nenhuma mensagem real, campanha, contato comercial ou oportunidade real foi usado no ensaio. Não houve push.

## Versões efetivamente publicadas

| Produto/processos | Revisão Git | Imagem local |
|---|---|---|
| vetorleads (API, worker e scheduler) | `29a8504af355878eedd47337df72d06e0ebbd407` | `vetorleads:chat129-29a8504af355` |
| Digest vetorleads | `sha256:3c7ab706546aac5ccbd12812b19a46be8262260ee06b59e8bb8d32b2ac9be22a` | Mesmo artefato nos três processos |
| vetoros (API, worker e scheduler) | `1700256f53fda990ed2f812af966c86cc50399ee` | `vetoros:chat129-1700256f53fd` |
| Digest vetoros | `sha256:eb6b85c0d694b4e30a6436ab0906ffc5fd4a9e868d60c0267872a289ed25dbfc` | Mesmo artefato nos três processos |

Configuração coordenada e gitlinks registrados no commit local de infraestrutura `066c3baf200399f059283e8c74d26e7cac41f406`. `docker-compose.override.yml` fixa as duas imagens por revisão e explicita `SUPPORT_AUTOMATION_WHATSAPP_DELIVERY_ENABLED=false` nos três processos VetorLeads. Credenciais, chaves, demais variáveis e volumes foram comparados com os containers anteriores e preservados. As imagens possuem rótulo `org.opencontainers.image.revision`; IDs/digests e saúde efetivamente observados estão em `chat129/published-images.json`.

API, worker e scheduler dos dois produtos foram atualizados. O Nginx foi validado com `nginx -t` e recarregado após cada grupo. **21 containers saudáveis** na conferência final; containers não relacionados conservaram seus IDs. Nenhuma sessão WAHA foi reiniciada.

## Backup e ensaio do banco

Backup: `/opt/infra-abrasil/backups/chat129/vetorleads-before.sql`, **522509 bytes**, permissões **0600** em diretório privado. SHA-256: `f046bf7a025f752409d91dd94366cfacfdab88fa5c81cf1f1bb8aa78d208fbfd`. O SQL e os arquivos privados de ambiente não foram adicionados ao Git. O arquivo temporário que transportou o ambiente produtivo para a migration já foi removido.

O backup foi restaurado em **chat129_rehearsal**, com usuário próprio restrito a esse banco. Foram conferidas **47 tabelas**. A primeira comparação exata com o banco vivo divergiu somente em `cache`, alterado normalmente pelo scheduler; as demais tabelas coincidiram. Isso foi diagnosticado antes de publicar. O ensaio aplicou somente as duas migrations pendentes; entre as tabelas existentes, apenas `migrations` mudou. As três novas tabelas nasceram vazias, sem perda comercial ou histórica.

Na operação foram aplicadas, nessa ordem:

1. `2026_10_19_200000_create_support_sandbox_tables` — conversas e mensagens sandbox.
2. `2026_10_19_210000_create_support_sandbox_accesses_table` — credenciais temporárias.

As migrations de automação `180000` e multicanal `190000` já estavam aplicadas. Não houve `migrate:fresh`, rollback de migrations ou suíte automatizada usando o banco operacional. Pest/PHPUnit continuou usando SQLite em memória com as proteções da CHAT-12.5; a infraestrutura real recebeu somente a homologação assistida dos endpoints sandbox, com identidade e dados sintéticos.

## Homologação publicada e isolamento

Foi criada uma identidade técnica temporária com papel owner **somente no tenant sintético**, sem root, sem acesso ao tenant operacional, sem convite ou envio de e-mail. As 14 regras de referência foram copiadas como inativas apenas para esse escopo. A sessão normal de login permitiu conferir o Laboratório e o widget publicados, em 1440 e 390 px.

O ensaio percorreu: seleção WebChat/regra inativa; emissão e resgate; intenção e resposta; conversa multimensagem; qualificação comercial simulada; prévia de contato/oportunidade; transferência; resposta humana interna; retomada com resposta automática; encerramento e bloqueio; revogação explícita; expiração e recarga sem fallback. Para observar a expiração sem esperar 30 minutos, antecipou-se somente `expires_at` de uma credencial pertencente ao tenant sintético, sem mudar relógio, TTL global ou acessos produtivos.

O histórico sintético foi exportado sem hashes de credenciais em `chat129/synthetic-sandbox-history.json`. Ao concluir, os acessos foram revogados e somente o tenant, o administrador e a sessão técnica dessa identidade foram removidos. Nenhum histórico real foi apagado. Todas as contagens abaixo voltaram ao estado anterior à homologação:

| Tabela | Antes | Depois |
|---|---:|---:|
| tenants | 1 | 1 |
| users | 2 | 2 |
| leads | 1 | 1 |
| lead_activities | 4 | 4 |
| web_conversations | 3 | 3 |
| web_chat_messages | 8 | 8 |
| web_chat_events | 5 | 5 |
| support_thread_states | 4 | 4 |
| support_automation_rules | 14 | 14 |
| support_automation_decisions | 2 | 2 |
| support_automation_outbox | 0 | 0 |
| support_whatsapp_receipts | 1 | 1 |
| jobs | 0 | 0 |
| failed_jobs | 0 | 0 |

Os checksums das tabelas originais comerciais, de histórico, regras e integrações foram preservados. As únicas diferenças globais ficaram em `migrations` (duas inclusões), `cache` e `sessions`. Em sessões, a coleta normal do Laravel removeu **48 registros já expirados**; a duração permaneceu em **120 minutos**, e **nenhuma sessão anterior ainda válida está ausente**. Também foram criadas sessões HTTP anônimas da navegação, que não são conversas ou mensagens operacionais. Não houve limpeza global de sessões ou alteração de APP_KEY. Evidências: `chat129/final-isolation.json` e `chat129/sessions-preservation.json`.

Capturas incluem Laboratório publicado, qualificação, operador nos dois lados, encerramento, revogação e expiração. A observação detalhada está em `chat129/published-sandbox-observations.json`; imagens estão no mesmo diretório.

## Regras e proposta de prioridade

As **14 regras do tenant operacional permaneceram inativas, com conteúdo, prioridades e agenda preservados**. Nenhum horário foi configurado.

Em cópias inativas do tenant sintético foram reproduzidos os conflitos e conferida esta proposta:

| Frase | Resultado atual | Proposta ensaiada | Resultado no Laboratório |
|---|---|---|---|
| “Como o sistema controla estoque?” | Apresentação do VetorOS | Apresentação geral: prioridade 10 → **200** | Peças e estoque |
| “Como funciona o financeiro e a nota fiscal?” | Vendas, caixa e financeiro | Notas fiscais: prioridade 80 → **65** | Notas fiscais |

A proposta ficou registrada para revisão, **sem aplicação ao tenant operacional ou ativação pública**. Evidência: `chat129/priority-proposal.json`. As condições de pagamento continuam sujeitas à confirmação comercial nos modelos revisados da CHAT-12.8; não houve atualização automática do conteúdo das regras produtivas já gravadas.

## WAHA e travas

A sessão do VetorLeads `vl-5f8726f2-5230-4e6c-b13b-69678d4ea517` permaneceu **WORKING**. `vetoros1-1` também permaneceu WORKING. `vetoros1-9` já estava **FAILED antes da publicação** e continuou assim; não foi modificada nem incluída artificialmente no resultado de homologação. As consultas WAHA foram somente GET de estado, sem envio ou reconexão. Detalhes antes/depois em `chat129/runtime-details-*.json`.

Trava global: **false**. Tenant operacional: `whatsapp_automation_enabled=0`, `whatsapp_automation_homologated_at=null`. Regras ativas: **0/14**. Outbox: **0** antes/depois. Leads, mensagens, eventos e decisões operacionais ficaram iguais. A publicação do Laboratório não habilitou atendimento automático público.

## Validações e correções de empacotamento

- Testes focados novamente executados fora da operação: **8 testes / 102 asserções** do laboratório e ponte; **9 testes / 29 asserções** do proxy, incluindo rejeições sem fallback.
- Evidências da CHAT-12.8 preservadas: regressões completas com **1.318 testes PHP VetorLeads e 820 VetorOS**, PHPStan sem erros e verificações de segurança/isolamento. Não foram repetidas no banco real.
- Snapshot da revisão VetorOS: **TypeScript aprovado**, **4 testes JavaScript aprovados**, lint dos arquivos alterados — incluindo `vite.config.ts` — aprovado.
- Lint completo mantido visível: **442 erros e 11 avisos preexistentes**, sem problemas nos arquivos alterados. Não houve desativação global de regras ou correção indiscriminada de arquivos. Artefatos `chat129/full-eslint.json` e `chat129/eslint-summary.json`.
- Builds Docker dos dois commits concluídos, incluindo os builds frontend de produção. Imagens finais sem `.env`, `.env.testing`, `node_modules`, Pest ou PHPUnit; dependências de desenvolvimento ficaram nos estágios de build.
- O primeiro build limpo falhou por falta dos diretórios vazios de cache. A publicação ficou suspensa, os Dockerfiles foram corrigidos e os builds passaram antes da atualização dos containers. O build VetorOS também passou a resolver Ziggy pela dependência já fixada no lockfile, sem depender do vendor local. Nenhuma funcionalidade de negócio nova foi criada.
- `.dockerignore` passou a excluir ambientes privados, sessões/cache locais e assets gerados; `.env.example` foi preservado. Contextos de build foram extraídos de `git archive HEAD`, sem backups, arquivos temporários ou alterações não versionadas.

## Git, comandos e reversão

Os commits locais foram separados em alterações anteriores de manutenção, migrations, código funcional/ponte, correções de empacotamento e configuração de infraestrutura. Lista exata de commits e arquivos adicionados: `chat129/commits.json`. Foram usados `git add -- <arquivos explícitos>` e `git commit -m <mensagem>`, sem `git add .`, descarte de alterações anteriores ou push. Os dois submódulos estão limpos e seus gitlinks correspondem às revisões publicadas. Evidências e este relatório são versionados separadamente da aplicação. Pastas históricas não rastreadas das etapas anteriores foram preservadas e não foram incluídas indiscriminadamente nesta publicação.

A construção usou os contextos de `git archive` e `docker build --label org.opencontainers.image.revision=<hash> -t <produto>:chat129-<hash> <contexto>`. Depois do ensaio, a migration foi executada por container efêmero da imagem nova; os grupos foram atualizados por `docker compose up -d --no-build --no-deps <api> <worker> <scheduler>`. Não foi utilizado `up` indiscriminado de toda a infraestrutura.

Reversão preparada em **`chat129/rollback.md`**, com overlay **`chat129/rollback-images.json`** e os IDs efetivamente anteriores. Ela revoga acessos sandbox, restaura primeiro o widget/proxy e depois a API anterior compatível, mantém as tabelas aditivas, preserva chaves/volumes e recarrega Nginx. Nenhum rollback foi executado.

## Pendências reais

1. Passivo de ESLint do VetorOS: 442 erros e 11 avisos, separado desta entrega.
2. Sessão WAHA `vetoros1-9` em FAILED: situação anterior, fora do escopo e preservada.
3. Propostas de prioridade aguardam revisão antes de qualquer alteração operacional.
4. Atendimento automático público e WhatsApp real continuam desabilitados; qualquer ativação futura exige etapa própria. Esta execução homologa somente o **sandbox publicado**.
