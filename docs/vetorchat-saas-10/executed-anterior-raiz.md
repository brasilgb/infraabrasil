# Execução — VETORCHAT-SAAS-09

Data: 22/10/2026. O `correio.md` atual é VETORCHAT-SAAS-09 e difere do VETORCHAT-SAAS-08 preservado. Esta etapa autoriza commit, push e deploy após verificações, mas não foi possível publicá-la por bloqueios ambientais de escrita Git e acesso à VPS.

**Resultado: BLOQUEADO antes de commits e deploy.** Não houve commit, push, alteração de gitlink, build de imagem, migration, operação Docker, mudança de VPS ou homologação de produção. A única entrada staged continua sendo a remoção preexistente de `gateway/abrasilsistemas/public/robots.txt`; a tentativa exata de staging para o commit A foi recusada antes de alterar o índice.

## Instrução e histórico

- VETORCHAT-SAAS-08: SHA-256 `eba8b743dd4fb8aaeea170b6f0ed911bbdad24f2a39144cc894d1a9b096feb70`, arquivada em `docs/vetorchat-saas-08/correio.md`.
- VETORCHAT-SAAS-09: SHA-256 `c83116351d62cf442f233cbab528a0d68cc6b74dcdfe682953ab2be50778902a`, arquivada em `docs/vetorchat-saas-09/correio.md`.
- Relatório anterior arquivado em `docs/vetorchat-saas-09/executed-anterior.md`; hashes em `docs/vetorchat-saas-09/instruction-hashes.txt`.

## Middleware e testes

Revisei `gateway/vetorleads/routes/settings.php` contra HEAD. As rotas preexistentes de automação e configuração WAHA foram movidas de um grupo protegido por `auth`, `verified`, `commercial:tenant` e `TenantWhatsappAdmin` para um novo grupo. Restaurei `commercial:tenant` ao grupo que contém essas rotas e acrescentei `chat.access:tenant`, preservando os controles anteriores. As novas rotas de produto/widget permanecem protegidas por `auth`, `verified`, `chat.access:tenant` e `TenantWhatsappAdmin`.

A primeira execução apontou que adicionar `commercial:tenant` também às novas rotas de produto impedia tenants de teste sem essa assinatura. Removi apenas esse middleware do grupo novo, conforme a política de acesso multitenant do Chat; não afrouxei as rotas preexistentes. A rodada final passou:

- ABrasil Sistemas: **81 testes, 1.016 assertions aprovados**.
- VetorLeads: **35 testes, 367 assertions aprovados** (`ChatProductTest`, `ChatProductsTest`, `TenantChatWidgetTest`, `VetorChatInstallationTest`).
- VetorOS Pest: **13 testes, 46 assertions aprovados**. A primeira tentativa falhou por falta de `APP_KEY`; repeti com `.env.testing` temporário derivado de `.env.testing.example` e chave sintética aleatória. O arquivo foi removido ao final.
- VetorOS TypeScript (`npm run types`): aprovado.
- VetorOS ESLint direcionado aos relatórios: aprovado.
- VetorPet: dependências `vendor`/`node_modules` indisponíveis; suíte não executada e dependências não instaladas.
- `git diff --check`: aprovado na raiz e nos quatro submódulos.

## Bloqueios de Git e publicação

1. Tentativa de staging seletivo do commit A conforme o manifesto foi recusada: o processo não consegue criar `.git/modules/gateway/abrasilsistemas/index.lock` porque o filesystem do `.git` está somente leitura. Nenhum arquivo foi staged por essa tentativa; não fiz commit local.
2. `origin/main` de ABrasil (`780bea4`), VetorOS (`370f08f`) e VetorPet (`e5b72ca`) foi consultado; os heads remotos coincidem com os HEADs locais. VetorLeads (`https://github.com/brasilgb/vetorleads.git`) exige autenticação e o Git não tem terminal: `could not read Username`. Não tentei contornar o controle de acesso.
3. Não há checkout local em `/opt/infra-abrasil`. SSH usando `srv1990862` falhou porque a configuração global `/etc/ssh/ssh_config.d/20-systemd-ssh-proxy.conf` tem proprietário/permissões inválidos. Ignorar a configuração com `-F /dev/null` também falhou: `srv1990862` não resolve como hostname. Não encontrei host ou IP utilizável no contexto local. Não foi possível confirmar backups, Compose, tags, volumes, WAHA ou executar deploy.
4. Como os commits não puderam ser criados/publicados e a VPS não está acessível, não atualizei gitlinks nem publiquei serviços. Verificações HTTP somente de leitura nesta continuação: ABrasil, `/sitemap.xml`, `/robots.txt`, VetorLeads, VetorOS e VetorPet responderam **200**; `https://vetorleads.com.br/widget.js` respondeu **404**. Inbox autenticada, APIs com chave real, WAHA e atendimento continuam sem homologação operacional.

## Estado final

A implementação e os testes locais revisados permanecem no working tree. O índice dos submódulos continua sem mudanças causadas por esta execução. O índice ABrasil conserva somente a deleção staged preexistente do `public/robots.txt`. `docker-compose.override.yml` e as tags registradas no relatório da etapa 08 não foram alterados. Nenhuma publicação deve ser considerada realizada.

Para retomar a publicação, esta sessão precisa de um ambiente com `.git` gravável, acesso autenticado já configurado ao remoto VetorLeads e conexão SSH válida para a VPS. Depois disso, repetir o staging seletivo do manifesto, criar/push dos commits e só então validar imagens, backups e deploy controlado.
