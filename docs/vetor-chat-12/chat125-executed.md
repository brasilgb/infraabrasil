# VETOR-CHAT-12.5 — Relatório de execução

**Resultado: HOMOLOGAÇÃO TÉCNICA APROVADA EM AMBIENTE ISOLADO.** `correio.md` foi comparado com a fase anterior: é o novo escopo CHAT-12.5, diferente do CHAT-12.4 arquivado em `docs/vetor-chat-12/chat124-correio.md`. O relatório anterior foi preservado em `docs/vetor-chat-12/chat124-executed.md`. A suíte integral, as verificações estáticas, o build e a revisão visual terminaram sem falhas. Nenhuma regra produtiva foi ativada e nenhuma mensagem real foi enviada.

## 1. Ambiente e dependências

- O host é a VPS `srv1990862` (Debian 13, kernel 6.12.113), e o checkout principal está em `/opt/infra-abrasil`; VetorLeads está em `gateway/vetorleads`. Não havia estação de desenvolvimento separada disponível. Os testes foram executados no host, fora dos containers produtivos, usando SQLite de teste/em memória.
- PHP 8.4.26, Node 20.19.2, npm 9.2.0 e cliente MariaDB estavam disponíveis. Composer não estava no PATH e o DNS externo não resolvia `getcomposer.org`. Composer 2.8.8 e a extensão intl foram extraídos de pacotes do espelho Debian configurado, sem instalação global.
- A instalação Composer temporária anterior continha as 156 versões do `composer.lock`, mas foi usada como raiz do diretório `vendor`, quebrando a descoberta da raiz de testes pelo Pest. O conteúdo foi copiado para `gateway/vetorleads/vendor`, e `composer install --no-interaction --no-progress --no-scripts --no-plugins` confirmou que nada precisava ser instalado/atualizado. `composer check-platform-reqs --lock` passou. O lockfile não foi alterado.
- `npm ci --offline --no-audit --no-fund` instalou 483 pacotes sem alterar `package-lock.json`. Houve avisos de `fchown` durante extração TAR, mas o comando terminou com código 0. `vendor` e `node_modules` permanecem no checkout para reproduzir as execuções.

## 2. Proteção do banco operacional

- A política compartilhada em `app/Support/TestDatabaseSafety.php` exige `APP_ENV=testing`; rejeita `DB_URL`; aceita SQLite em memória/arquivo de teste ou MySQL/MariaDB com host de teste, banco e usuário terminados em `_test`; e exige fila síncrona, mail/cache/session em memória e automação comercial desligada.
- `tests/Support/safe-test-bootstrap.php` aplica a verificação antes do autoload dos testes. O `AppServiceProvider` verifica também configuração em cache e a configuração efetiva antes de comandos/migrations no ambiente de teste. `phpunit.xml` fixa os valores isolados.
- Provas executadas diretamente: `TestDatabaseSafetyTest` passou (4 testes, 7 assertions); PHPUnit direto passou (1 teste, 2 assertions). Uma invocação deliberadamente incompleta de `php artisan migrate --force` foi recusada pelo guard (`queues, mail, cache, session, and commercial flags must use isolated test settings`), antes da migration. Outro teste Pest cobre a recusa de banco operacional por configuração sem abrir conexão real.
- A suíte foi executada com SQLite em memória; não houve credenciais MySQL de teste nem grants de banco criados ou usados. Não se tentou conectar ao banco operacional a partir dos testes.

## 3. Diagnóstico das 27 falhas anteriores

As falhas registradas anteriormente eram 3 em CustomerOnboarding, 21 em Prospecting, 1 em TenantCommercialIsolation e 2 em TrialExpiredAutomation. A configuração local `.env` do checkout contém valores de produção (`APP_ENV=production`, fila/cache/session em banco, mail SMTP e `SAAS_COMMERCIAL_ENABLED=true`). Isso é consistente com os sintomas: dashboard comercial em vez de tenant, jobs de prospecção permanecendo enfileirados e estado/sessão comercial divergente dos fixtures. O bootstrap/configuração de teste anterior não impedia de forma suficiente que essa configuração contaminasse execuções.

Com ambiente de teste fixado e isolado, os 27 casos passam. Nenhuma assertion foi enfraquecida, nenhum teste foi desabilitado e não foi necessário mascarar falha funcional. O guard de segurança foi endurecido como correção preventiva para execuções diretas de Pest/PHPUnit e migrations.

## 4. Testes e análises

- Regressão PHP completa, em SQLite isolado: **1.310 passaram, 0 falharam; 13.861 assertions**.
- `SupportAutomationTest` + `SupportMultichannelTest`: 25 testes passaram na execução direcionada. Ambos também estão incluídos na suíte completa final.
- `TestDatabaseSafetyTest`: 4 passaram; PHPUnit direto de segurança: 1 passou.
- PHPStan: passou, 0 erros (`--debug --no-progress`).
- TypeScript (`npm run types:check`): passou.
- ESLint global (`npm run lint:check`): passou.
- Build Vite: passou, 2.413 módulos; saída temporária em `/tmp/vetorleads-chat125-build`. Nenhuma dependência da aplicação foi atualizada.

## 5. Revisão visual

- Chromium/Playwright com dados e contas sintéticos percorreu 9 jornadas em desktop (1440 px) e 9 em celular (390 px): Dashboard, captura de prospects, prospects, CRM, Central de Atendimento, contatos recebidos, Automação de Atendimento/simulador, perfil e configurações de leads.
- As 18 rotas responderam HTTP 200; `results.json` registra `errors: []` e nenhuma largura excedida. Não houve erros de console ou página. O simulador exibiu prévia sintética e informou que não grava conversa, lead, mensagem ou outbox.
- Evidências e script reproduzível estão em `docs/vetor-chat-12/chat125-interface/` (18 screenshots, `results.json`, `browser.cjs` e `seed-demo.php`). A base sintética foi removida após a revisão; as capturas não contêm dados de clientes.

## 6. Homologação multicanal

- `SupportAutomationTest` e `SupportMultichannelTest`, além da suíte completa, cobrem os fluxos de canais, regras, qualificação, memória, transferência/retorno humano, outbox, reconciliação, opt-out e isolamento por tenant com simuladores/dados de teste.
- O simulador visual foi executado como prévia sem persistência. A consulta posterior ao SQLite sintético encontrou regra inativa e zero decisões, itens de outbox, conversas, mensagens ou leads.
- Não houve envio real nem ativação das 14 regras produtivas.

## 7. Verificação operacional somente leitura

Na VPS, serviços foram consultados sem executar testes em containers e o banco foi lido em transação somente leitura com rollback. No estado observado em 19/10/2026 às 17:44 UTC: serviços Docker listados estavam saudáveis; tenant ativo Anderson Brasil estava habilitado; chave de automação WhatsApp do tenant estava desligada; 14 regras existentes estavam inativas (0 ativas, 0 agendadas); operador owner estava ativo; sessão WAHA aparecia `connected` com 1 conexão provisionada; outbox estava vazia; flag global efetiva de entrega WhatsApp era `false`. Não houve alteração de linhas. A consulta confirma estado atual, não histórico de envios.

## 8. Versionamento e publicação

- Alterações acumuladas desde CHAT-11 continuam locais, incluindo código de VetorLeads/VetorOS, migrações, interface e os arquivos de proteção/testes desta fase. A auditoria detalhada de alterações do submódulo está disponível pelo `git status` em `gateway/vetorleads`; nada foi staged ou commitado.
- `correio.md` e `executed.md`, os arquivos de evidência em `docs/vetor-chat-12/` e `.correio.md.swp` aparecem no status da raiz. O swap é temporário e não deve ser versionado. Segredos e `.env` não devem ser incluídos.
- O build foi local; não houve criação/publicação de imagem, deploy, commit ou push. Os containers produtivos não usam o checkout como mount de código, e seus estados operacionais permaneceram saudáveis durante as consultas.
- O diretório `public/build` foi restaurado ao estado anterior à inspeção visual.

## Pendências

Nenhuma etapa do critério técnico CHAT-12.5 ficou pendente. A homologação valida regressão e simulação isoladas; não autoriza ativação de regras nem envio real, que permaneceram fora desta execução.
