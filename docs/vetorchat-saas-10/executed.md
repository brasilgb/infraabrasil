# Execução — VETORCHAT-SAAS-09 (continuação operacional)

Data: 22/10/2026, ~18:00–18:45 (BRT). Instrução: `correio.md` (SHA-256 `66e9de22…6fc2`, igual a `docs/vetorchat-saas-10/instruction-hashes.txt`). O relatório anterior da raiz está em `docs/vetorchat-saas-10/executed-anterior-raiz.md`.

**Resultado: os quatro produtos estão publicados.** VetorLeads e ABrasil Sistemas já tinham sido publicados antes desta continuação e não foram refeitos. VetorOS e VetorPet foram publicados agora. Desgarrados não foi alterado.

## Incidente durante a etapa: OOM e reboot da VPS

O primeiro build do VetorOS, por volta de 17:54, levou a VPS a um OOM global. Ela reiniciou às 18:01.

- **Causa:** o `/tmp` é tmpfs (RAM). Cópias de validação com `vendor`/`node_modules` somavam ~2,9 GB ali. Junto com o `vite build` e os containers de produção, numa máquina de 7,8 GB **sem swap**, a memória acabou.
- **Impacto:** todos os containers foram reiniciados pela política `unless-stopped` e voltaram `healthy`. Bancos e volumes ficaram preservados.
- **Correções:**
  - swap de 4 GB em `/swapfile` (`fstab`, `vm.swappiness=10`);
  - validações passam a rodar em `/var/tmp/vetor-scratch` (disco);
  - `docker builder prune` liberou 24,4 GB;
  - `.dockerignore` do VetorOS passa a excluir `public_vetoros.zip` e `public-old/` (contexto de 655 MB → 411 MB);
  - builds feitos um serviço por vez.

  Nos builds seguintes o swap não foi usado.

## Estado por produto

| Produto | Commit | Imagem em execução | Migrations | HTTP | Classificação |
|---|---|---|---|---|---|
| VetorLeads | `853e2c4` | `vetorleads:vetorchat09-853e2c4` | 59, 0 pendentes (última `create_chat_products`) | `/`, `/login`, `/up`, `/widget.js`, `robots.txt`, `sitemap.xml`: 200 | **Publicado** |
| ABrasil Sistemas | `fc2d8c5` | `infra-abrasil-abrasilsistema{,-worker,-scheduler}:latest` (build de 17:3x) | 25, 0 pendentes (última `add_seo_blog_category`) | `/`, `/blog`, `/servicos`, `robots.txt`, `sitemap.xml`: 200 | **Publicado com pendências** (VetorChat sem configuração) |
| VetorOS | `8ec770d7` | `vetoros:eqtypes-8ec770d` | 184, 0 pendentes (última `create_efi_integration_tests_table`) | `/`, `/login`, `/build/manifest.json`, `robots.txt`, `sitemap.xml`: 200 | **Publicado com pendências** (homologação funcional e sessões WAHA) |
| VetorPet | `acb97e7` | `infra-abrasil-vetorpet{,-worker,-scheduler}:latest` (build de 18:37) | 75, 0 pendentes (nenhuma nova) | `/`, `/login`, `robots.txt`, `sitemap.xml`: 200 | **Publicado com pendências** (VetorChat sem chave; teste de estoque) |
| Desgarrados | `dad5134` | inalterada | — | — | **Não alterado** (por instrução) |

Detalhes do VetorLeads e do ABrasil estão em `docs/vetorchat-saas-09/executed.md` e `docs/abrasil-deploy-01/executed.md`.

## VetorOS

- **Commits publicados:** `850590aa` (código da etapa), `305b2b30` (`.dockerignore`) e `8ec770d7`. Este último corrige os rótulos de Configurações → Tipos de equipamento, que diziam "Equipamento", e renomeia o campo Mobile/PC/Outro para "Grupo". Os três estão no `origin/main`.
- **Override:** estava fixado em `vetoros:chat129-1700256f53fd`, imagem inexistente (pendência do relatório 09). Foi refixado em `vetoros:eqtypes-8ec770d` nos três serviços.
- **Backups:**
  - `backups/vetoros_pre_deploy_305b2b3_20261022_181855.sql`;
  - `backups/vetoros_pre_deploy_8ec770d_20261022_182916.sql`;
  - os dois com `.sha256` ao lado e `-- Dump completed` no fim.
- **Rollback:** as imagens `vetoros:vetorchat10-305b2b3` e `vetoros:pull-370f08f1` foram preservadas. Para voltar, basta trocar a tag no override e rodar `up -d --no-deps --no-build` nos três serviços. Não houve migrations novas.
- **Testes (cópia isolada):**
  - 882/883 aprovados; a falha `OrderFilesIsolationTest` vem do GD sem suporte a JPEG na imagem PHP (ambiental);
  - `tsc`, Prettier e `vite build` aprovados.
- **Verificação:**
  - os três containers estão `healthy`;
  - o manifest servido é o mesmo da imagem;
  - os APKs em `/apk/` respondem 200;
  - sem ERROR/exception nos logs.

## VetorPet

- **Diff `21c283b..acb97e7`:** filtro por vendedor no relatório de OS e injeção opcional do widget VetorChat nas páginas públicas. Não há migrations nem dependências novas.
- **Dependências validadas:**
  - `yarn install --frozen-lockfile` e `yarn build` OK;
  - `tsc` com 1 erro preexistente em `ssr.tsx` (`ziggy-js`), arquivo que não mudou;
  - o widget só é injetado com `VETORCHAT_WIDGET_KEY` de 48 caracteres. Hoje a chave tem 0 caracteres, então o widget não aparece (comportamento seguro, confirmado no HTML de `/`).
- **Testes:** rodados com um MySQL 8.4 temporário em rede isolada, já removido. Resultado: 224/231 aprovados, incluindo os novos `OrderReportDateRangeTest` e `VetorChatInstallationTest`.
  - 6 falhas são o mesmo problema de GD/JPEG (ambiental).
  - 1 falha é `MvpWorkflowTest > order editing recalculates stock…`: a API aceita uma edição de pedido com estoque insuficiente (200 em vez de 409). Ela é preexistente, porque o diff publicado não toca a API de pedidos.
- **Backup:** `backups/vetorpet_pre_deploy_acb97e7_20261022_183626.sql`, com `.sha256`.
- **Rollback:** `docker tag infra-abrasil-<svc>:rollback-21c283b infra-abrasil-<svc>:latest` para `vetorpet`, `vetorpet-worker` e `vetorpet-scheduler`, seguido de `up -d --no-deps --no-build` e reload do nginx.
- **Publicação:**
  - build sequencial dos três serviços;
  - `up -d --no-deps --no-build`;
  - `nginx -t` OK e reload;
  - os três `healthy`, sem erros nos logs.

## Homologação transversal

| Item | Resultado |
|---|---|
| `https://vetorleads.com.br/widget.js` | 200 `application/javascript`, 19.337 B |
| API do widget: chave inexistente de 48 hex | 403 "Widget indisponível ou domínio não autorizado" |
| API do widget: chave malformada | 404 |
| robots.txt e sitemap.xml dos 4 produtos | 200 com content-type correto |
| WAHA | container `healthy` e não reiniciado manualmente; sessão do VetorLeads `WORKING` |
| WAHA: sessões `vetoros1-1` e `vetoros1-9` | `FAILED`: estavam `STOPPED` (nunca pareadas), o WAHA as religou no reboot e o QR expirou sem leitura |
| Logs (15 min) de vetorleads, abrasilsistema, vetoros e vetorpet | 0 ERROR/CRITICAL/exception |
| Containers `infra-abrasil-*` | todos `healthy` |
| Inbox e comunicação ponta a ponta | **não executada**: não há widget ativo nem identidade de teste autorizada. Nenhum dado de teste foi criado |

## Pendências comprovadas

1. **VetorChat sem configuração** (ABrasil e VetorPet):
   - criar e ativar o widget do tenant no VetorLeads;
   - cadastrar os produtos `abrasil` e `vetorpet` com seus domínios;
   - definir `VETORCHAT_WIDGET_KEY` no `.env` da infra e referenciá-la no Compose;
   - recriar só os serviços web.

   Só então o teste ponta a ponta e o inbox podem ser homologados.
2. **VetorOS:**
   - a homologação funcional do `correio.md` do VetorOS (login, OS, portal público, PDV, isolamento entre empresas, modal Efí) não foi feita com usuário logado;
   - as sessões WAHA `vetoros1-1` e `vetoros1-9` precisam de leitura de QR pelos tenants, ou de remoção se forem abandonadas.
3. **VetorPet:** o bug preexistente de validação de estoque na edição de pedido pela API (`MvpWorkflowTest`).
4. **Imagens PHP:** o GD foi compilado sem JPEG (`imagejpeg` ausente) no VetorOS e no VetorPet. Isso faz falhar testes de upload/geração de imagem e pode afetar a geração de JPEG em produção.
5. **Infra sem commit:** `docker-compose.override.yml` (pins do VetorLeads e do VetorOS), gitlink do VetorOS (`8ec770d7`), este relatório e os relatórios em `docs/`.
6. **Desgarrados:** o gitlink `f87a15e` não existe no remoto. Fica fora desta etapa.
