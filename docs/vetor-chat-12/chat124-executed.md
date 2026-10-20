# VETOR-CHAT-12.4 — Relatório de execução

**Resultado: NÃO APROVADO.** O banco foi auditado em modo somente leitura, a proteção inicial dos testes foi adicionada e as verificações frontend passaram. A suíte PHP completa e a homologação visual não puderam ser concluídas; portanto, a regressão permanece bloqueada e as 27 falhas anteriores seguem sem diagnóstico confirmado.

## 1. Auditoria do banco operacional

- Conexão confirmada: banco `vetorleads`, servidor `28f4f55b745b`, auditado em 19/10/2026 às 17:20 UTC. A auditoria executou consultas dentro de `START TRANSACTION READ ONLY` e terminou com `ROLLBACK`.
- Estado observado: 51 migrations; 1 tenant; 1 lead; 61 resultados de prospecção; 2 buscas; 3 conversas WebChat; 7 mensagens WebChat; 14 regras; 1 decisão; 14 auditorias; outbox vazia; 1 conexão WhatsApp; 3 estados de conversa.
- Reconferência às 17:xx UTC: 14 regras ativas/inativas = **0/14**; WhatsApp por tenant desligado; outbox sem registros. A variável global `SUPPORT_AUTOMATION_WHATSAPP_DELIVERY_ENABLED` está ausente e o padrão no código é `false`. A auditoria não enviou mensagens.
- O backup `backups/vetorleads_pre_chat121_20261019.sql`, de 15:52 UTC, contém 48 migrations e antecede mudanças legítimas da CHAT-12. A comparação confirma 3 migrations posteriores e, em tabelas WebChat, aumento de 2 para 3 conversas e de 6 para 7 mensagens. O backup não contém as tabelas de automação, criadas posteriormente.
- As contagens principais (1 lead e 61 resultados) coincidem com a auditoria registrada na CHAT-12.3. Isso não prova ausência de alterações em campos/linhas: o backup não é imediatamente anterior ao incidente e não existe snapshot transacional do instante da tentativa. O relatório anterior documenta que um comando de teste herdou a conexão operacional; não é possível determinar com certeza se todas as operações de teste foram revertidas. Não encontrei evidência de perda em massa nas contagens atuais. Nenhum registro comercial foi apagado, restaurado ou editado nesta execução.

## 2. Proteção dos testes

- Adicionado `tests/Support/TestDatabaseSafety.php`, que exige `APP_ENV=testing`, `DB_URL` vazio e SQLite em memória/arquivo de teste ou banco com sufixo `_test`.
- Adicionado `tests/Support/safe-test-bootstrap.php` como bootstrap PHPUnit, antes do autoload do Laravel e de `RefreshDatabase`; conexões fora da política terminam com código 78.
- `phpunit.xml` agora aponta para esse bootstrap. Adicionado teste Pest cobrindo a rejeição de `vetorleads` sem abrir conexão.
- Verificação manual isolada da função recusou a configuração `mysql/vetorleads`; o bootstrap aceitou a configuração SQLite em memória. A execução do teste Pest não foi concluída: o diretório Composer temporário disponível está incompleto/inconsistente e o Pest não conseguiu localizar a raiz de testes. O container operacional não possui Pest (`artisan test` não está definido), e não instalei dependências nele.
- Assim, a proteção está implementada e a verificação direta passou, mas a prova automatizada executada pelo próprio Pest continua pendente. Não foi feita nova tentativa de teste contra conexão operacional.

## 3. Falhas anteriores e correções

As 27 falhas do relatório CHAT-12.3 permanecem abertas: CustomerOnboarding (3), Prospecting (21), TenantCommercialIsolation (1) e TrialExpiredAutomation (2). Não foi seguro afirmar causa nem corrigir expectativas sem conseguir reproduzir a suíte PHP isolada. Nenhum teste foi desabilitado.

## 4. Verificações concluídas

- ESLint global (`npm run lint:check`): passou.
- TypeScript (`npm run types:check`): passou.
- Build Vite: passou, 2.413 módulos, com saída em `/tmp/vetorleads-chat124-build`; Wayfinder foi executado usando o diretório temporário Composer. Uma primeira tentativa sem autoload falhou antes da compilação.
- Os cinco apontamentos ESLint da extensão Firefox foram corrigidos em `extension/ab-prospect-firefox/tests/extension.test.mjs` (espaçamento e chaves; comportamento preservado).
- PHPStan não concluiu neste host: o processo falhou ao abrir socket TCP local. A tentativa anterior de Pest também confirmou que as dependências temporárias atuais não permitem rodar testes com segurança reprodutível.

## 5. Homologação visual

Não realizada. Não houve execução confirmada de Chromium/Playwright nesta fase; desktop e celular, jornadas, formulários, estados vazios e mensagens de erro permanecem sem inspeção visual.

## 6. Infraestrutura e canais

- `docker ps` mostrou VetorLeads (app, worker e scheduler), VetorOS e os demais serviços listados como `healthy` no momento da consulta.
- 14 regras inativas; automação WhatsApp por tenant desligada; outbox vazia; variável global ausente com padrão de código `false`.
- Estado atual da sessão WAHA e estado individual dos operadores não foram reconfirmados nesta execução; não declarar esses itens homologados.
- Não houve ativação de regras, envio real, commit, push ou deploy.

## 7. Alterações locais e pendências

Arquivos adicionados nesta execução: `gateway/vetorleads/tests/Support/TestDatabaseSafety.php`, `gateway/vetorleads/tests/Support/safe-test-bootstrap.php`, `gateway/vetorleads/tests/Unit/TestDatabaseSafetyTest.php`. Alterados nesta execução: `gateway/vetorleads/phpunit.xml` e `gateway/vetorleads/extension/ab-prospect-firefox/tests/extension.test.mjs`. As alterações acumuladas anteriores em VetorLeads/VetorOS foram preservadas; não foram descartadas nem publicadas.

**Pendências obrigatórias antes de aprovação:** obter dependências Composer completas em ambiente isolado; executar a prova Pest da barreira e confirmar que a invocação falha antes de qualquer conexão operacional; diagnosticar/corrigir os 27 casos; repetir testes direcionados e suíte completa; executar PHPStan; homologar desktop/celular em Chromium; reconfirmar sessão WAHA e operadores. Até lá, a fase não está aprovada.
