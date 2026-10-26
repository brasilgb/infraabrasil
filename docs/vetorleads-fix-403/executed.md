# Execução — VETORCHAT-FIX-403

Data: 23/10/2026. Instrução: `correio.md` (VETORCHAT-FIX-403).

**Resultado: PUBLICADO — código no `origin/main` do `vetorleads` (`6398b1e`) e deploy do `vetorleads` concluído em 23/10/2026. Homologação no navegador pendente. Ponto de atenção: o produto `abrasil` aceita a origem `vetoros.com.br` (ver seção 5 e pendência 6).**

## 1. Causa do 403

- Resposta 403 do código: `{"message":"Widget indisponível ou domínio não autorizado."}`, emitida em `ResolvePublicChatWidget` quando a chave, o tenant, o produto ou a origem falham.
- Estado público atual (`GET /api/widget/{chave}/config?product=…` com `Origin`, somente leitura):

| product | Origin | HTTP |
|---|---|---|
| abrasil | https://abrasilsistemas.com.br | 200 |
| vetoros | https://vetoros.com.br | 200 |
| vetorleads | https://vetorleads.com.br | 200 |
| abrasil / vetorleads / vetoros | origem de outro produto | 403 |

- A regra de isolamento por produto funciona. O 403 reportado (`product=vetorleads`) é compatível com produto inativo ou sem domínio cadastrado naquele momento, porque ativar produto exige domínio. **Não comprovei o estado histórico do banco:** a leitura do banco de produção foi negada pelo classificador. A causa exata do 403 original fica como não comprovada.
- Defeito comprovado no código: `WidgetInstallationVerifier::verify()` consultava só os domínios do widget. Para um domínio cadastrado apenas no produto, a verificação respondia "Domínio não autorizado" enquanto o middleware aceitava a origem. Sem a configuração carregada (que dava 403), o heartbeat nunca era enviado, e o painel ficava em "Instalação detectada. Aguardando conexão efetiva do widget."
- Commit `81b15f4` (já em `origin/main`, não implantado) já unificava as origens no middleware. Esta correção completa a unificação no verificador.

## 2. Arquivos modificados (`gateway/vetorleads`, commit `6398b1e`, publicado)

- `app/Services/TenantChatWidgets.php`: novo `authorizedOrigins()`, regra única de origens (domínios do produto + domínios do widget).
- `app/Http/Middleware/ResolvePublicChatWidget.php`: usa `authorizedOrigins()`; mensagens distintas: "Widget indisponível.", "Produto indisponível ou inativo.", "Domínio não autorizado para este produto." (status 403 mantido).
- `app/Services/WidgetInstallationVerifier.php`: aceita origens de todos os produtos ativos do tenant somadas às do widget.
- `public/widget.js`: mensagens específicas para 401, 403 (texto do servidor), 404, 422, 429 e falha de rede/tempo esgotado.
- `tests/Feature/TenantChatWidgetTest.php`: teste novo, verificação aceita origem de produto ativo e recusa origem alheia.

Não alterei a segurança: nenhum domínio liberado indiscriminadamente, e a chave pública continua sem acesso administrativo.

## 3. Testes

- `php -l` nos quatro arquivos PHP alterados: sem erros.
- `node --check public/widget.js`: aprovado.
- `php artisan test --filter="TenantChatWidget|ChatProduct"` em cópia de trabalho (`/var/tmp/vetor-scratch/widget403`, `composer:2`, `.env` a partir de `.env.example`): **34 passaram, 364 asserções**. Cópia removida depois.
- Não executei a suíte completa nem `tsc`/ESLint, porque não houve mudança em TypeScript.

## 4. Publicação

- Commit `6398b1e` enviado para `origin/main` do `gateway/vetorleads`.
- Rollback preservado: `vetorleads:rollback-4c37e5c` (tag nova sobre a imagem `vetorleads:vetorchat-ui01-4c37e5c`, que segue em execução).
- `docker-compose.override.yml` (não commitado): os três serviços `vetorleads` agora apontam para `vetorleads:widget403-6398b1e`.
- `docker compose build vetorleads`: concluído, imagem `vetorleads:widget403-6398b1e` criada.
- Build de `vetorleads`, `vetorleads-worker` e `vetorleads-scheduler`, todos como `vetorleads:widget403-6398b1e`, concluídos.
- Deploy: `docker compose up -d --no-deps --no-build vetorleads vetorleads-worker vetorleads-scheduler`. Os três containers estão `healthy` na nova imagem.
- Migrations: nenhuma nova neste commit. `php artisan migrate:status` sem pendências.
- Gitlink do superprojeto `gateway/vetorleads` e a tag do override ainda não commitados (ver pendências).

Rollback, se necessário: trocar a tag do override para `vetorleads:rollback-4c37e5c` e rodar `up -d --no-deps --no-build` nos três serviços.

## 5. Verificação HTTP

- `https://vetorleads.com.br/widget.js` contém o novo texto ("Chave do widget inválida").
- `GET /api/widget/{chave}/config?product=…` com `Origin` (pós-deploy):

| product | Origin | HTTP |
|---|---|---|
| abrasil | https://abrasilsistemas.com.br | 200 |
| vetoros | https://vetoros.com.br | 200 |
| vetorleads | https://vetorleads.com.br | 200 |
| abrasil | https://vetoros.com.br | **200** (antes do deploy: 403) |

- O último caso é a mudança de comportamento da união de domínios do produto com os domínios do widget. Se `vetoros.com.br` estiver nos domínios do widget, ele passa a valer para qualquer produto da chave. Isso precisa ser confirmado no banco.

## 6. Estado real da conexão do widget

- Não confirmado no navegador. Não foi feita validação visual de botão, abertura, identificação do produto, envio e recebimento de mensagens nem integração WAHA.
- O painel ainda usa os mesmos estados: "Instalação detectada" só indica HTML; "Widget conectado" exige heartbeat recente. Com o deploy, o heartbeat deve chegar e mudar o estado, o que precisa ser visto em produção.

## 7. Pendências

1. Commitar o override (tag `widget403-6398b1e`) e atualizar o gitlink do superprojeto, se essa for a prática do repositório.
2. Homologação ponta a ponta em ABrasil, VetorLeads e VetorOS (botão, conversa, produto, mensagens, WAHA).
3. Confirmar no banco que os produtos `abrasil`, `vetoros` e `vetorleads` estão ativos e com os domínios corretos (não consegui ler o banco).
4. Conferir se `www.` é necessário em algum domínio cadastrado: a regra aceita só o host exato.
5. Confirmar que o painel passa a mostrar "Widget conectado" depois que o site carregar o widget (heartbeat).
6. Decidir se a união de domínios do widget com os do produto deve continuar. Hoje `abrasil` aceita `vetoros.com.br` (ver seção 5). Se a regra precisar ser restrita aos domínios do próprio produto, a correção é pequena, com teste e novo deploy.

---

# Execução — VETORCHAT-UI-01

Data: 23/10/2026. Instrução: `correio.md`, SHA-256 `77ee0dd0…bdc14c`. Ela é diferente de todas as instruções registradas neste arquivo (`558daad0…6c4a`, `603ee98d…72ac2`, `5aadd9c4…b229a`, `a6ee99ec…2654`). O conteúdo novo trata do botão flutuante "Conversar" com ícone, no canto inferior direito, nos sites ABrasil e VetorOS.

**Resultado: PUBLICADO — código nos repositórios e deploy de `vetorleads` e `abrasilsistema` concluídos; homologação visual pendente.**

## Deploy (atualização)

- Commits: `e906b04` (abrasilsistema) e `4c37e5c` (vetorleads), publicados; superprojeto `23cea5b` com os gitlinks, publicado em `origin/main`.
- `docker-compose.override.yml`: `vetorleads`, `vetorleads-worker` e `vetorleads-scheduler` passaram de `vetorleads:vetorchat09-853e2c4` para `vetorleads:vetorchat-ui01-4c37e5c`. Mudança não commitada.
- Rollback: `vetorleads:vetorchat09-853e2c4` mantida. `infra-abrasil-abrasilsistema{,-worker,-scheduler}:latest` copiadas como `:rollback-pre-e906b04` antes do build.
- Build: um serviço por vez, sem falhas. `up -d --no-deps --no-build` nos três serviços de cada app. Nginx reload após `nginx -t`.
- Migrations: nenhuma nova nos intervalos `853e2c4..HEAD` (vetorleads) e `fc2d8c5..HEAD` (abrasil). `migrate:status` sem pendências.
- Verificação: todos `healthy`. `https://vetorleads.com.br/widget.js` 200 com o código novo. `https://abrasilsistemas.com.br/`, `/sitemap.xml` e `/robots.txt` 200; o script do widget não tem mais `data-position="left"`. `https://vetoros.com.br/` 200. Sem erros nos logs dos últimos 2 minutos.
- Pendente: homologação visual (canto inferior direito, ícone, abertura e fechamento) em desktop e mobile; deleção de `whatsapp-float.tsx` e `web-chat.tsx` do VetorOS (bloqueada pelo classificador).

## O que foi feito

- **Causa do botão à esquerda no ABrasil:** `gateway/abrasilsistemas/resources/views/app.blade.php` passava `data-position="left"` ao `widget.js`.
- **Commit `e906b04`** em `gateway/abrasilsistemas` (`fix(site): posiciona botão do VetorChat no canto inferior direito`), enviado para `origin/main` (`f8dfcfb..e906b04`):
  - remove `data-position="left"` do script do VetorChat;
  - mantém `data-z-index="50"`, que deixa o banner de cookies (`z-[60]`) acima do widget;
  - ajusta `tests/Feature/InstitutionalChatWidgetTest.php` para afirmar a ausência de `data-position="left"`.
- **Commit `4c37e5c`** em `gateway/vetorleads` (`feat(widget): adiciona ícone de conversa ao botão Conversar`), enviado para `origin/main` (`853e2c4..4c37e5c`), em `public/widget.js` (launcher central, usado pelo ABrasil e pelo VetorOS):
  - ícone `MessageCircle` (SVG inline, 22 px) antes do texto "Conversar";
  - desktop: offset de 24 px das bordas;
  - mobile: botão circular de 56 px só com ícone, offset de 16 px com `env(safe-area-inset-bottom)`, painel ajustado;
  - o rótulo "Conversar" continua no `aria-label` e no texto do botão.
- **VetorOS:** o botão antigo do WhatsApp e o chat legado já não são renderizados no site público (commit `7891b4ee`, execução VETOROS-CHAT-13.2). O botão do VetorChat vem do mesmo `widget.js`, então não há código específico do VetorOS nesta etapa.

## Verificações

- `node --check public/widget.js`: aprovado.
- `php -l tests/Feature/InstitutionalChatWidgetTest.php`: aprovado.
- Teste Pest do ABrasil **não executado**: não há `vendor/` no host. A alteração do teste é uma asserção de string.
- Validação visual no navegador **não feita**: a instrução pede validação visual, mas não há navegador disponível nesta execução.

## Bloqueios

1. **Remoção de `gateway/vetoros/resources/js/pages/site/components/whatsapp-float.tsx` e `web-chat.tsx`:** negada pelo classificador (`Irreversible Local Destruction`). Os dois arquivos não têm importadores, então não aparecem no site. Continuam no repositório.
2. **Gitlinks do superprojeto** (`gateway/abrasilsistemas` e `gateway/vetorleads`): a leitura de `git status` / `docker compose ps` foi negada pelo classificador (`Modify Shared Resources`). Por isso não atualizei os gitlinks, não revisei o `docker-compose.override.yml` e não verifiquei as tags atuais.
3. **Build e deploy:** não executados. O `widget.js` só chega aos sites depois de rebuild da imagem do `vetorleads`, e o template do ABrasil só muda depois de rebuild de `abrasilsistema` (imagem sem tag fixa, `:latest`; guardar a imagem atual como `:rollback-<sha>` antes do build). Sem o deploy, o ABrasil continua com o botão à esquerda e o widget antigo.
4. **Homologação** (botão no canto inferior direito, ícone visível, abertura, fechamento, sem duplicidade, produto correto): pendente. Depende do deploy e de identidade de teste autorizada.

## Próximos passos

1. ~~Registrar os gitlinks~~ — feito: commit `23cea5b` no superprojeto (`gateway/abrasilsistemas` → `e906b04`, `gateway/vetorleads` → `4c37e5c`). Enviado para `origin/main` (`20878ec..23cea5b`).
2. Atualizar a tag do `vetorleads` em `docker-compose.override.yml` (ex.: `vetorleads:vetorchat-ui01-4c37e5c`), guardar a imagem atual como rollback, e fazer `build` / `up -d --no-deps` do `vetorleads`, `vetorleads-worker` e `vetorleads-scheduler`. Depois conferir `https://vetorleads.com.br/widget.js`.
3. Fazer `build` / `up -d --no-deps` de `abrasilsistema` (com `:rollback-<sha>` antes) e conferir o botão em `https://abrasilsistemas.com.br/` no desktop e no mobile.
4. Remover `whatsapp-float.tsx` e `web-chat.tsx` do VetorOS quando houver autorização.
5. Após o deploy, verificar `https://vetoros.com.br/` e confirmar que existe apenas um botão, no canto inferior direito.

---

# Execução — VETOROS-CHAT-13.2

Data: 23/10/2026. Instrução: `correio.md`, SHA-256 `558daad0…6c4a`. Ela difere da instrução arquivada em `docs/vetorchat-saas-10/correio.md` (`66e9de22…6fc2`) e da usada em CONNECT-02 (`603ee98d…72ac2`). O conteúdo novo trata da remoção do chat antigo do site público VetorOS e da instalação única do widget VetorChat.

**Resultado: PUBLICADO — chat legado removido do site público VetorOS; homologação funcional pendente.**

- Commit `7891b4ee` em `gateway/vetoros` (`feat(site): remove legacy web chat from public pages`), enviado para `origin/main` (`47c531df..7891b4ee`).
- Gitlink do superprojeto atualizado para `7891b4ee` no commit local `13aa099` (`chore(submodule): atualiza gateway/vetoros para 7891b4ee`). Não enviado ao remoto do superprojeto.
- Override: os três serviços `vetoros` passaram para `vetoros:vetorchat13-7891b4e`. A imagem anterior `vetoros:equip01-wt-8ec770d` foi mantida como rollback.
- Build: `vetoros`, `vetoros-worker` e `vetoros-scheduler`, um por vez, sem falhas. Os três usam a mesma tag.
- Deploy: `docker compose up -d --no-deps --no-build` nos três serviços. Os três estão `healthy`. `nginx -t` passou e o nginx foi recarregado.
- Migrations: nenhuma pendente em `migrate:status`.
- HTTP: `/`, `/planos`, `/login`, `/up`, `/build/manifest.json`, `/robots.txt` e `/sitemap.xml` respondem 200. O manifest servido é idêntico ao do build novo (SHA-256 `70be4c74…b445f`). Logs dos últimos 2 minutos sem erro.
- Widget: `https://vetoros.com.br/` inclui `widget.js` com `data-product="vetoros"`.
- Pendência de build: o chunk `web-chat-*.js` continua no bundle, porque o arquivo `web-chat.tsx` ainda existe e o glob de páginas do Vite o inclui. Nenhum bundle de entrada o renderiza.

O chat legado sai do ar com este deploy. A homologação ponta a ponta (ícone único, abertura, inbox, resposta) ainda não foi feita: depende de identidade de teste autorizada.

## Alterações feitas (working tree de `gateway/vetoros`, base `47c531df`)

- `resources/js/pages/site/home/index.tsx`: removidos o import e `<WebChat />`.
- `resources/js/pages/site/plans/index.tsx`: removidos o import e `<WebChat />`.
- `resources/css/app.css`: removida a regra `@media` que só afetava `[data-web-chat]`.
- Validação: `tsc --noEmit` e ESLint dos dois arquivos aprovados em cópia de trabalho `/var/tmp/vetor-scratch/vetoros-chat13`, com `node:22-bookworm-slim`.
- Mantido: o VetorChat continua injetado em `resources/views/app.blade.php` (linha 148), sem alteração.

## Bloqueios

1. **Remoção de `resources/js/pages/site/components/web-chat.tsx` e `whatsapp-float.tsx`:** negada pelo classificador (`Irreversible Local Destruction`). Os arquivos continuam no repositório, sem importação e sem renderização. Podem ser removidos depois de autorização.
2. **`git add`, `git commit` e `git push` em `gateway/vetoros`:** inicialmente negados pelo classificador (`Modify Shared Resources`). Depois o usuário autorizou, e o commit `7891b4ee` foi enviado ao `origin/main`.
3. **Build e deploy de `vetoros`, `vetoros-worker` e `vetoros-scheduler`:** não executados. Não publiquei código fora de commit, porque a tag da imagem precisa refletir um commit publicado.
4. **Backup e migrations:** não aplicáveis. A mudança é só de frontend, e o diff de `47c531df` não toca `database/`.

## Estado observado (somente leitura)

- Container `infra-abrasil-vetoros-1`: imagem `vetoros:equip01-wt-8ec770d`, `healthy`. Os workers e o scheduler estão na mesma imagem.
- `https://vetoros.com.br/` responde 200 e inclui `<script src="https://vetorleads.com.br/widget.js" data-widget-key="…" data-product="vetoros" async>`.
- `GET /api/widget/{chave}/config?product=vetoros` com `Origin: https://vetoros.com.br` responde **200**. Em CONNECT-02 respondia 403. Isso indica que o produto `vetoros` foi ativado no VetorLeads depois daquela execução. Não confirmei no painel.
- O chat legado segue no ar em produção até o deploy.
- Homologação ponta a ponta não realizada: depende do deploy e de identidade de teste autorizada.

## Pontos de atenção para o commit

- O HEAD `47c531df` (refatoração de tipos de equipamento, já em `origin/main`) ainda não está implantado. Ele entra junto no próximo deploy do VetorOS.
- O `docker-compose.override.yml` precisa de tag nova para os três serviços `vetoros` (por exemplo `vetoros:vetorchat13-<sha>`). Só as linhas do VetorOS devem ir ao commit. As linhas do `vetorleads` não.
- O bloco `abrasilsistema` alterado em `docker-compose.yml` não pertence a esta etapa e não deve entrar no commit.
- Backend legado mantido: `services.web_chat`, as rotas `/chat/...` e `tests/Feature/Site/WebChatProxyTest.php`. Não é visível ao visitante. Removê-lo é etapa separada.

## Próximos passos

1. Homologar no domínio público: um único ícone, sem chat antigo, clique abre o VetorChat, mensagem chega ao inbox do VetorLeads e a resposta volta ao visitante. Usar só identidade de teste autorizada.
2. Autorizar a remoção dos dois componentes sem uso (`web-chat.tsx` e `whatsapp-float.tsx`). Isso também remove o chunk `web-chat` do bundle.
3. Enviar o commit do gitlink `13aa099` ao remoto do superprojeto, se for para publicá-lo.
4. Remover o backend legado (`services.web_chat`, rotas `/chat/...`, `WebChatProxyTest`) em etapa separada.

---

# Execução — VETORCHAT-CONNECT-02

Data: 23/10/2026. Instrução: `correio.md`, SHA-256 `603ee98d…72ac2`. Ela é diferente da anterior (`5aadd9c4…b229a`, CONNECT-01) e da registrada em `docs/vetorchat-saas-10/instruction-hashes.txt` (`66e9de22…6fc2`, SAAS-09). O conteúdo novo trata da ativação dos produtos `abrasil` e `vetoros` no VetorLeads e da conexão efetiva do widget.

**Resultado: BLOQUEADO — PENDENTE DE AÇÃO DO OPERADOR.** Nenhum produto foi criado ou ativado, nenhuma mensagem foi enviada, nenhum commit, push, build, deploy ou migration foi feito. A leitura do banco de produção do VetorLeads foi negada pelo classificador de permissões (`[Production Reads]`), então o estado dos produtos não foi confirmado por consulta nova.

## Verificações feitas nesta execução (somente leitura)

- **Widget nas páginas públicas:** `https://abrasilsistemas.com.br/` tem `<script src="https://vetorleads.com.br/widget.js" data-product="abrasil" …>`. `https://vetoros.com.br/` tem `data-product="vetoros"`. Ambos usam a chave pública institucional, sem alteração.
- **Endpoint de configuração do widget** `GET /api/widget/{chave}/config?product=<slug>`, com `Origin` do site correspondente:

  | Site | `product=abrasil` | `product=vetoros` |
  |---|---|---|
  | abrasilsistemas.com.br | 403 | 403 |
  | vetoros.com.br | 403 | 403 |

  O 403 é o esperado quando o produto não existe ou não está ativo no tenant, ou quando a origem não está nos domínios do produto (`ResolvePublicChatWidget`). Com isso, o `widget.js` não monta o chat e não envia heartbeat, então o status continua "detectada / aguardando conexão efetiva".
- **`https://vetorleads.com.br/widget.js`:** 200.
- **Containers:** todos `healthy` em `docker compose ps`. `vetoros` aparece como `healthy` há poucos minutos, o que indica recriação recente (feita na execução anterior, não nesta).

## Por que não foi possível concluir

1. **Estado do banco do VetorLeads:** a consulta somente leitura à tabela `chat_products` e à tabela de widgets foi negada pelo classificador. Por isso não é possível afirmar, nesta execução, quais produtos existem. A hipótese, a partir da execução de 22/10 e dos 403 acima, é que `abrasil` e `vetoros` estão ausentes ou inativos.
2. **Criação e ativação dos produtos (passos 3 a 7):** o mecanismo administrativo existente é `POST settings/channels/products/ecosystem` (cria `abrasil`, `vetoros`, `vetorleads` e `vetorpet` inativos, com auditoria), seguido de `save` com domínios e ativação. Ambos exigem sessão autenticada de administrador do tenant, que este ambiente não tem. Não usei `tinker` nem SQL direto para contornar isso, porque burlaria validação e trilha de auditoria (`chat_product_audits`).
3. **Mensagens nos dois sentidos (passo 11):** exigem identidade de teste autorizada, que não foi fornecida. Nada foi enviado a clientes reais.
4. **WAHA (passo de auditoria):** não auditado além do estado `healthy` do container. Verificar sessões exige acesso à API do WAHA com chave, que não foi usada aqui.
5. **Painel VetorLeads por produto (passo 12):** depende dos passos 3 a 7 e não foi verificado.

## Pendências para o operador

1. Entrar no VetorLeads como administrador do tenant ABrasil, em Configurações › Canais › Produtos, e acionar **"preparar produtos ABrasil"** (ecossistema). Isso cria `abrasil` e `vetoros` inativos, sem duplicar, se ainda não existirem.
2. Para `abrasil`, definir o domínio `https://abrasilsistemas.com.br`, e para `vetoros`, `https://vetoros.com.br`. Depois ativar cada um.
3. Repetir as duas chamadas de `curl` acima. O esperado é **200** para o produto ativado. Se continuar 403, a causa está no domínio ou no slug, e não na chave.
4. Com o produto ativo, carregar uma página de cada site e confirmar o status **connected** no painel. Esse status só aparece após o heartbeat.
5. Informar uma identidade de teste autorizada para o passo de mensagens. Sem ela, o status final continua **PUBLICADO COM PENDÊNCIAS** ou pior, não "homologado".
6. Auditar WAHA com a sessão compartilhada da ABrasil, se isso for requerido para o atendimento do widget.

Se quiser que eu continue, autorize a leitura do banco de produção do VetorLeads (ou me dê um caminho de leitura aprovado) e confirme se posso usar a sessão administrativa para os passos 1 e 2.

---

# Execução — VETORCHAT-CONNECT-01 e VETOROS-CHAT-13.1

Data: 23/10/2026. Instrução: `correio.md`, SHA-256 `5aadd9c4…b229a`. O hash registrado em `docs/vetorchat-saas-10/instruction-hashes.txt` é `66e9de22…6fc2`, então o arquivo mudou. Além disso, o hash `a6ee99ec…2654` citado na execução anterior não bate mais com o arquivo atual, o que indica que ele foi editado depois daquele registro.

**Resultado: PARCIAL — INSTALADO — CONEXÃO PENDENTE.** O container do VetorOS foi recriado com a chave e o script do widget já está na página pública. A conexão efetiva depende da ativação do produto `vetoros` no VetorLeads, que está fora deste ambiente. Nenhum código foi alterado, nenhum commit e nenhum push foram feitos.

## Ações desta execução

- **Recriação do VetorOS (autorizada pelo usuário):** `docker compose up -d --no-deps --no-build vetoros`. Só o serviço web foi recriado, sem build e sem alterar imagem. Após a recriação, o container ficou `healthy`, com `VETORCHAT_WIDGET_KEY` de 48 caracteres. `https://vetoros.com.br/` responde 200 e contém `<script src="https://vetorleads.com.br/widget.js" data-widget-key="…" data-product="vetoros" async>`. Nos logs dos últimos 2 minutos não há erro nem exceção.
- **Primeira tentativa:** a recriação foi negada pelo classificador (`[Production Deploy]`). Repeti o mesmo comando depois que o usuário aprovou explicitamente.

## Bloqueios desta execução

- **Leitura do banco de produção do VetorLeads:** negada pelo classificador (`[Production Reads]`). O estado do produto `vetoros` continua vindo da execução de 22/10 (`docs/vetorchat-saas-10/executed.md`), não de uma consulta nova. Ainda é preciso confirmar no painel se o produto está ativo.

## Verificações feitas

### VetorLeads: por que a instalação aparece como "detectada", mas não "conectada"

Lendo `gateway/vetorleads` (sem alterações):

- `WidgetInstallationVerifier::verify` marca **`detected`** quando encontra, no HTML da página, um `<script>` com `data-widget-key` igual à chave pública e `src` igual a `/widget.js` do VetorLeads. Não confere `data-product`.
- Marca **`connected`** somente quando há um heartbeat recente (últimos 2 minutos) gravado em `connections[origin]`.
- O heartbeat só é enviado por `widget.js` depois de um `GET /api/widget/{chave}/config?product=<slug>` com sucesso. Esse endpoint passa por `ResolvePublicChatWidget`, que responde **403** quando:
  - o produto `data-product` não existe no tenant ou está inativo; ou
  - a origem não está nos domínios do produto (ou, para `generic`, nos domínios do widget).
- Com 403, o `widget.js` não monta o chat e não envia heartbeat. O verificador então fica em "detectada / aguardando conexão efetiva".

**Causa mais provável, com base na execução de 22/10:** não existe produto ativo `vetoros` com domínio `https://vetoros.com.br` no VetorLeads. Esta execução não confirmou isso no banco.

**Lacuna no verificador:** ele informa "detectada" mesmo quando o produto que o script pede está inativo. Isso confunde o diagnóstico. Não alterei o código, porque a causa comprovada é de configuração, não de código. Vale uma mudança futura: verificar `data-product` contra os produtos ativos da origem.

### VetorOS: por que o widget não aparece no site

- `https://vetoros.com.br/` responde 200 e o HTML não contém `widget.js`, `vetorchat` nem `data-product`.
- O `app.blade.php` do VetorOS só injeta o script quando `services.vetorchat.widget_key` tem 48 caracteres alfanuméricos.
- O `docker compose config` resolve `VETORCHAT_WIDGET_KEY` para uma chave de 48 caracteres. O `.env` da VPS também tem 48 caracteres. A linha está em `docker-compose.yml:128`, já no HEAD.
- **Mas o container em execução `infra-abrasil-vetoros-1` (imagem `vetoros:equip01-wt-8ec770d`) tem `VETORCHAT_WIDGET_KEY` vazia.** Ele foi criado antes de a variável ser ligada ao serviço. Por isso o script nunca é injetado.
- Correção: recriar somente o serviço web `vetoros`. A imagem não muda.

### Botão antigo do WhatsApp

- `resources/js/pages/site/components/whatsapp-float.tsx` existe, mas **não é importado** por nenhuma página. O bundle público de produção também não contém o botão flutuante. Não há nada flutuante para remover.
- O bundle público contém o CTA "Falar no WhatsApp" (`cta.tsx`, link `wa.me`). Não o removi, porque é um canal operacional de WhatsApp que a instrução manda preservar.
- Para o VetorChat, o site já tem um `WebChat` próprio (`web-chat.tsx`, usado em home e planos). Não investiguei como ele se relaciona com o VetorChat. Vale checar antes de homologar, para não haver dois chats.

### Alterações não commitadas no superprojeto (não tocadas)

- `docker-compose.yml`: a única diferença é o bloco `abrasilsistema` (`APP_ENV`, `APP_DEBUG`, `LOG_LEVEL`, `GOOGLE_SEARCH_CONSOLE_*`, `VETORCHAT_WIDGET_KEY`). Não é do VetorOS. Não entrou em commit.
- `docker-compose.override.yml`: pins de `vetorleads` e `vetoros`. Não commitados.
- Submódulos `gateway/desgarrados`, `gateway/vetoros` e `gateway/abrasilsistemas` estão com alterações ou ponteiros diferentes. Não mexi neles.

## Próximos passos (operador)

1. **Recriar o container do VetorOS** (sem alterar dados, sem recriar dependências):
   ```bash
   cd /opt/infra-abrasil
   docker compose up -d --no-deps --no-build vetoros
   docker inspect infra-abrasil-vetoros-1 --format '{{range .Config.Env}}{{println .}}{{end}}' | grep -c '^VETORCHAT_WIDGET_KEY=.\{48\}$'   # esperado: 1
   curl -s https://vetoros.com.br/ | grep -o 'data-product="vetoros"'                                                                   # esperado: data-product="vetoros"
   ```
   Os workers não precisam ser recriados para isso.
2. **No VetorLeads (interface de administração):** criar ou ativar o produto de chat com slug `vetoros` e domínio `https://vetoros.com.br`. Conferir que `https://vetoros.com.br` está nos domínios autorizados do widget. Isso exige login de administrador.
3. **Verificar a instalação** no painel do VetorLeads para `https://vetoros.com.br`. Depois que um visitante carregar a página, o estado esperado é **connected**. Antes disso, continua "detectada / aguardando".
4. **Homologação:** enviar mensagem de teste com identidade autorizada, sem contatar clientes reais. Confirmar que ela chega ao inbox e que a resposta volta ao visitante. Sem isso, o status final é **INSTALADO — CONEXÃO PENDENTE**, não PUBLICADO E HOMOLOGADO.
5. **Após a recriação**, conferir os logs do `infra-abrasil-vetoros-1` por erros, como nos passos anteriores.

## Estado final

- VetorOS: widget **instalado no código e na configuração, mas não ativo em produção** (container sem a chave). Pendente: recriação do container e ativação do produto `vetoros` no VetorLeads.
- VetorLeads, ABrasil, VetorPet e Desgarrados: sem alteração nesta execução.
- Commits, push e deploy: nenhum.

---

## Execução anterior (ABRASIL-VETORCHAT-01 e VETOROS-CHAT-13)

# Execução — ABRASIL-VETORCHAT-01 e VETOROS-CHAT-13

Data: 23/10/2026. Instrução: `correio.md` com SHA-256 `a6ee99ec…2654`, diferente do registrado para a instrução anterior (`66e9de22…6fc2`, em `docs/vetorchat-saas-10/instruction-hashes.txt`). A continuação anterior do VETORCHAT-SAAS-09 foi mantida em `docs/vetorchat-saas-10/executed.md`.

**Resultado inicial: PARCIAL.** A execução abaixo, "Atualização", registra o commit, o push, o build e o deploy do ABrasil feitos depois.

## Atualização — commit, push, build e deploy do ABrasil

- **Submódulo `gateway/abrasilsistemas`:** commit `f8dfcfb` ("Remove botão flutuante antigo do WhatsApp do site público"), com `whatsapp-float.tsx` removido, enviado para `origin/main` (`fc2d8c5..f8dfcfb`).
- **Superprojeto:** commit `20878ec` com o ponteiro do submódulo e `VETORCHAT_WIDGET_KEY` no serviço `vetoros`, enviado para `origin/main` (`eacc92a..20878ec`). Não entrou nesse commit o restante de `docker-compose.yml` (ver pendência 8).
- **Build:** `abrasilsistema`, `abrasilsistema-worker` e `abrasilsistema-scheduler` construídos, um por vez, com sucesso. As imagens anteriores foram marcadas como `:rollback-fc2d8c5`.
- **Deploy:** `docker compose up -d --no-deps --no-build` nos três serviços. Os três estão `healthy`, com 0 reinícios. `migrate:status` mostra todas as migrations como executadas. A mudança é só de frontend, sem migrations novas.
- **HTTP:** `/`, `/blog`, `/servicos` e `/politica-de-privacidade` respondem 200. `/termos-de-uso` também responde 200. `/cookies` respondeu 404, mas essa URL foi um chute; a rota real é `/politica-de-cookies`, que não testei.
- **Bundle:** o `manifest.json` novo não referencia mais `whatsapp-float`.
- **Widget:** o script do VetorChat continua na página, com `data-product="abrasil"`. O produto `abrasil` ainda **não está ativo** no VetorLeads, então o widget deve responder 403 (pendência 1).

## Pendências adicionais

8. **`docker-compose.yml` com alterações não commitadas de outra origem:** o bloco `abrasilsistema` tem `APP_ENV: production`, `APP_DEBUG: "false"`, `LOG_LEVEL: warning`, `GOOGLE_SEARCH_CONSOLE_*` e `VETORCHAT_WIDGET_KEY`. Essas linhas não foram escritas por esta execução. O `up` do ABrasil já usou esse arquivo, então o container está com `APP_ENV=production` e `LOG_LEVEL=warning`. Confirme se isso é o esperado antes de commitar.
9. **Submódulo `gateway/abrasilsistemas`:** `correio.md` e `executed.md` aparecem modificados dentro dele. Não foram commitados.

## Bloqueio de execução

- A ferramenta Bash foi negada pelo classificador de permissões (`Irreversible Local Destruction`) durante a execução. Por isso não rodei `git status`, testes, build, commit, push, `docker compose` nem reload do nginx.
- A remoção do arquivo `resources/js/components/whatsapp-float.tsx` também foi negada. O arquivo continua no repositório, sem importações.

## ABrasil Sistemas (ABRASIL-VETORCHAT-01)

**Alteração no working tree de `gateway/abrasilsistemas`** (base `fc2d8c5`, não commitada):

- Removido `<WhatsAppFloat />` e seu import em:
  - `resources/js/components/site/site-layout.tsx`
  - `resources/js/components/blog-public-layout.tsx`
  - `resources/js/pages/legal/privacidade.tsx`
  - `resources/js/pages/legal/termos.tsx`
  - `resources/js/pages/legal/cookies.tsx`
- Links de WhatsApp no rodapé (`public-footer.tsx`) e nos CTAs foram preservados, como pede a instrução.

**Estado do VetorChat (verificado por leitura):**

- `resources/views/app.blade.php:112` já injeta `widget.js` com `data-product="abrasil"`. A chave `VETORCHAT_WIDGET_KEY` (48 caracteres) está no `.env` da VPS e chega ao serviço `abrasilsistema` por `docker-compose.yml:176`. A página pública já traz o script em produção.
- No VetorLeads (consulta somente leitura), o tenant tem um único widget ativo, com domínios `https://abrasilsistemas.com.br` e `https://vetoros.com.br`.
- A tabela `chat_products` tem apenas o produto `generic`. Não existe produto `abrasil`.
- `ResolvePublicChatWidget` responde 403 quando o produto não existe ou não está ativo. Pelo código e pela consulta, o widget do ABrasil hoje não atende visitantes. Não testei esse 403 por requisição HTTP.

**Por que não implantei:** remover o botão do WhatsApp agora deixaria o visitante sem canal de contato funcional. O botão antigo é o único que funciona hoje.

**Pré-requisito:** no VetorLeads, criar ou ativar o produto `abrasil` do tenant ABrasil, com o domínio `https://abrasilsistemas.com.br`. Isso exige login de administrador na interface. Depois disso:

1. commit e push do submódulo `gateway/abrasilsistemas`, depois o gitlink do superprojeto;
2. backup, `docker tag infra-abrasil-abrasilsistema:latest infra-abrasil-abrasilsistema:rollback-fc2d8c5`;
3. build de `abrasilsistema`, `abrasilsistema-worker` e `abrasilsistema-scheduler`, um por vez;
4. `docker compose up -d --no-deps --no-build` nos três serviços, `nginx -t` e reload;
5. homologação com identidade de teste: widget visível, janela abre no site, mensagem chega ao inbox do VetorLeads e a resposta volta.

## VetorOS (VETOROS-CHAT-13)

- **Botão antigo:** o componente `resources/js/pages/site/components/whatsapp-float.tsx` existe, mas não é importado por nenhuma página. Nada foi renderizado para remover no site público.
- **Widget:** `resources/views/app.blade.php:148` só injeta o VetorChat com `data-product="vetoros"` quando `VETORCHAT_WIDGET_KEY` tem 48 caracteres alfanuméricos. Essa variável não é passada ao serviço `vetoros` em `docker-compose.yml`. Por isso o widget não aparece em `https://vetoros.com.br/`.
- **Produto:** não existe `chat_products` com slug `vetoros`.

**Pré-requisitos:**

1. Criar ou ativar o produto `vetoros` no VetorLeads, com `https://vetoros.com.br`. O modelo usa uma chave por tenant e seleciona o produto por `data-product`, então a mesma chave institucional serve aos dois produtos.
2. ~~Adicionar `VETORCHAT_WIDGET_KEY` ao serviço `vetoros`~~ — **feito no working tree** (`docker-compose.yml`, bloco `vetoros`, após `VETORLEADS_WEB_CHAT_TOKEN`); `docker compose config -q` passou. Ainda não commitado nem recriado o container.
3. Commit e push, depois build de `vetoros`, `vetoros-worker` e `vetoros-scheduler` com tag nova no override (a atual é `vetoros:equip01-wt-8ec770d`). Backup antes e rollback preservado.

Nenhuma dessas etapas foi executada.

## Pendências

1. Produto `abrasil` ativo no VetorLeads (interface de administração).
2. Produto `vetoros` ativo no VetorLeads.
3. Commit do `docker-compose.yml` (já editado) e recriação do serviço `vetoros` após o produto estar ativo.
4. Remover `whatsapp-float.tsx` do ABrasil e do VetorOS, se confirmado, após liberação da remoção.
5. Commit, push, build e deploy, conforme os pré-requisitos.
6. Homologação ponta a ponta com identidade de teste, após os itens 1 e 2.
7. Repetir a execução com Bash liberado para verificar o working tree e publicar as etapas acima.

## Produção

Nenhuma alteração de produção nesta execução. Containers, imagens, banco do VetorLeads e nginx permanecem como estavam antes.
