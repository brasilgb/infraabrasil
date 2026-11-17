# Execução — VETOROS-WAHA-FIX-01 e VETORCHAT-NATIVE-01

Data: 23/10/2026. Instrução: `correio.md`, diferente da anterior (VETORCHAT-FIX-403, arquivada em `docs/vetorleads-fix-403/executed.md`).

**Resultado:**
- **VETOROS-WAHA-FIX-01: PUBLICADO.** Código em `origin/main` do `vetoros` (`1cea06d`), gitlink do superprojeto em `af7cf07` e deploy concluído em 23/10/2026.
- **VETORCHAT-NATIVE-01: PUBLICADO.** Código em `origin/main` do `vetorleads` (`215abb9`), gitlink do superprojeto publicado e deploy concluído em 23/10/2026. Homologação visual no navegador pendente.

## VETOROS-WAHA-FIX-01

### 1. Diagnóstico

Verificações somente leitura na VPS:

- WAHA (`infra-abrasil-waha-1`, `2026.8.2`, NOWEB): `healthy`, `/health` respondendo 200 a cada 15 s.
- Conectividade `vetoros` → `waha:3000`: `GET /api/sessions`, `/api/sessions/vetoros1-1` e `/api/version` responderam 200 com a `X-Api-Key` do container. Não há falha de rede, URL ou chave.
- Estado das sessões (`WhatsappConnection` local e WAHA):

| tenant | sessão | status WAHA | status local | última falha |
|---|---|---|---|---|
| 1 | vetoros1-1 | FAILED | failed | 23/10 10:44 |
| 7 | vetoros1-7 | FAILED | failed | 23/10 12:43 |
| 9 | vetoros1-9 | — | qr_required | 17/10 |
| 2 | vetoros1-2 | — | disconnected | — |

- Logs do WAHA da sessão `vetoros1-7` (11:46 a 11:49): sessão iniciada, QR gerado, depois "QR code has not been scanned yet, force stopping the session" e "connection errored: QR refs attempts ended". Ou seja, o QR expirou sem leitura e o WAHA marcou a sessão como `FAILED`.
- Logs da sessão `vetoros1-1`: logout às 18:52 e `POST /api/sessions` retornou 422 "already exists", com fallback para `restart` (201). O fluxo de reinício do código funciona.
- Chamada `GET /api/{sessão}/auth/qr` numa sessão `FAILED` retorna 422 "Session status is not as expected" após ~10 s.

**Causa raiz:** a sessão falha por QR Code não lido dentro da janela do WAHA (~2 min). O status `FAILED` é mapeado corretamente para `failed` e a tela exibia a mensagem fixa "Não foi possível conectar. Verifique o serviço do WAHA e tente novamente.", que sugere indisponibilidade do WAHA. O WAHA estava saudável.

**Não comprovado:** se, para o usuário, o QR não era lido por falta de clareza da tela, ou se a imagem falhava no navegador. A validação visual não foi feita (sem navegador nesta execução).

### 2. Alteração

- `gateway/vetoros/resources/js/pages/app/whatsapp-message/connection-panel.tsx`: a mensagem de `failed` passou a dizer que a sessão foi encerrada, geralmente porque o QR Code não foi lido a tempo, e orienta a clicar em "Conectar WhatsApp".
- Commit `1cea06da` em `gateway/vetoros` (`fix(whatsapp): explica sessão encerrada por QR não lido`), enviado para `origin/main`.

Não alterei: sessões WAHA existentes (`vetoros1-1`, `vetoros1-7` seguem `FAILED`), credenciais, configuração de webhook, isolamento de tenants e o backend.

### 3. Testes

- Prettier 3.3.3 no arquivo alterado: executado em container `node:22-bookworm-slim`; a saída foi cortada e o resultado não foi confirmado. **Pendente de reconfirmação.**
- Testes PHP/feature de `WhatsappConnectionControllerTest`: não executados (a alteração é só de texto no frontend).
- Build/TypeScript do VetorOS: não executado.

### 4. Ainda não feito (itens do correio)

- Estados de tela pedidos (conectado, desconectado, aguardando QR, iniciando, erro de autenticação, WAHA indisponível): só o estado `failed` foi tratado. Distinguir "WAHA indisponível" exige o backend informar o motivo, o que não foi implementado.
- Ações de atualizar status e desconectar com confirmação: não implementadas. Desconectar já existe, sem confirmação.
- Teste funcional da conexão com QR real: depende de escaneamento pelo operador.

### 5. Publicação (executada)

- Push `7891b4ee..1cea06da` em `gateway/vetoros`.
- Gitlink do superprojeto atualizado para `1cea06d` (commit `af7cf07`, enviado para `origin/main`).
- Rollback preservado: `vetoros:rollback-7891b4e` (mesma imagem de `vetoros:vetorchat13-7891b4e`).
- `docker-compose.override.yml` (não commitado, como antes): `vetoros`, `vetoros-worker` e `vetoros-scheduler` apontam para `vetoros:waha-fix-1cea06d`.
- `docker compose build vetoros vetoros-worker vetoros-scheduler`: concluído.
- `migrate:status` na nova imagem: sem pendências.
- `up -d --no-deps --no-build` nos três serviços: `healthy`. `nginx -s reload`: concluído.
- Verificação: o bundle `connection-panel-CUEEUDVd.js` contém o novo texto; `https://vetoros.com.br/` e `/login` respondem 200.
- Sessões WAHA não foram alteradas. Tenants 1 e 7 precisam clicar em "Conectar WhatsApp" e escanear o QR dentro de ~2 min.
- Rollback, se necessário: trocar a tag no override para `vetoros:rollback-7891b4e` e rodar `up -d --no-deps --no-build` nos três serviços.

## VETORCHAT-NATIVE-01

**Resultado: PUBLICADO (`gateway/vetorleads` `215abb9`, deploy em 23/10/2026). Homologação no navegador pendente.**

### 1. Diagnóstico

- A landing (`resources/views/app.blade.php`) já injeta `widget.js` com `data-product="vetorleads"` e a chave institucional. Esse trecho não estava quebrado.
- O WebChat no canal web não chama o WAHA: `SupportAutomation` só entrega por WhatsApp quando `whatsappEnabled()` é verdadeiro. Início, envio e identificação de conversa não dependem do WAHA.
- A origem do próprio site precisava estar cadastrada manualmente em `domains` do produto `vetorleads`. Sem esse cadastro, o middleware respondia 403 ("Produto indisponível ou inativo" / "Domínio não autorizado para este produto"). É a causa estrutural de indisponibilidade do chat no VetorLeads, que a correção anterior (FIX-403) apenas explicou.
- Não comprovei o estado histórico do banco de produção; a leitura foi bloqueada pelo classificador em execução anterior.

### 2. Alteração (commit `215abb9`, local)

- `app/Services/TenantChatWidgets.php`: `authorizedOrigins()` inclui a origem do `APP_URL` quando o produto é `vetorleads`, via novo `nativeOrigin()`.
- `app/Services/WidgetInstallationVerifier.php`: usa a mesma regra para todos os produtos ativos do tenant.
- `tests/Feature/TenantChatWidgetTest.php`: teste novo, que aceita a origem própria sem domínio cadastrado, recusa origem alheia e recusa o produto `abrasil` inexistente no tenant.

Não alterei: isolamento multitenant, chave pública (continua sem permissão administrativa), contratos da API, nem o widget externo dos outros produtos.

### 3. Testes

- Cópia de trabalho em `/var/tmp/vetor-scratch/native-01` (removida depois), `composer:2`, com ambiente de teste isolado (SQLite `:memory:`, queue sync, mail array).
- `php artisan test --filter="TenantChatWidget|ChatProduct|VetorChatInstallation|WebChatApi"`: **54 passaram, 474 asserções**.
- Não executado: suíte completa, ESLint/tsc (sem mudança de frontend), e navegador (sem validação visual).

### 4. Itens do correio: estado

| Item | Estado |
|---|---|
| Chat disponível na landing sem cadastro manual | Feito (origem própria autorizada) |
| Tenant institucional resolvido pelo backend | Já existia (chave institucional + `data-product`) |
| Não depender do WAHA para WebChat | Já existia (verificado no código) |
| Identificação `vetorleads` como produto de origem | Já existia |
| Botão flutuante no canto inferior direito | Já existe pelo launcher do widget. **Não adicionei outro**, para evitar duplicidade |
| Central de atendimento (nome, telefone, histórico, CRM, transferência, não lidas) | Não revisada nesta execução. Requer inspeção da tela `web-chat` |
| Widget externo preservado (ABrasil, VetorOS, VetorSales) | Não alterado; testes de origem cobrem o comportamento |
| Fluxos ponta a ponta (1 a 8 do correio) | **Não validados no navegador.** Cobertos por testes de feature apenas em parte |

### 5. Publicação (pendente de aprovação)

Passos quando aprovado, seguindo a mesma sequência do ajuste WAHA:

1. `git -C gateway/vetorleads push origin main` (fast-forward sobre `6398b1e`).
2. Atualizar o gitlink do superprojeto.
3. Nova tag no `docker-compose.override.yml` (ex.: `vetorleads:native-01-215abb9`), com `docker tag` da imagem atual como `vetorleads:rollback-6398b1e` antes do build.
4. `docker compose build vetorleads vetorleads-worker vetorleads-scheduler`, `migrate:status` sem pendências (não há migration nova neste commit), depois `up -d --no-deps --no-build` nos três e `nginx -s reload`.
5. Validar `https://vetorleads.com.br/` e `GET /api/widget/{chave}/config?product=vetorleads` com `Origin: https://vetorleads.com.br` (200).


### 6. Publicação (executada)

- Push `6398b1e..215abb9` em `gateway/vetorleads`.
- Gitlink do superprojeto atualizado para `215abb9` e publicado.
- Rollback: `vetorleads:rollback-6398b1e` (mesma imagem de `vetorleads:widget403-6398b1e`).
- Override (não commitado, como antes): `vetorleads`, `vetorleads-worker` e `vetorleads-scheduler` em `vetorleads:native-01-215abb9`.
- Build dos três serviços: concluído. `migrate:status`: sem pendências.
- `up -d --no-deps --no-build` nos três: `healthy`. `nginx -s reload`: concluído.
- Verificação HTTP: `https://vetorleads.com.br/` 200; `config?product=vetorleads` com Origin próprio 200; com Origin alheio 403.
- Pendente: validação visual no navegador (botão, conversa, resposta do atendente, central) e revisão da central `web-chat`.
- Rollback, se necessário: trocar a tag do override para `vetorleads:rollback-6398b1e` e rodar `up -d --no-deps --no-build` nos três serviços.

## Correção pós-publicação: 403 em requisições same-origin (Firefox)

- Causa comprovada pelo DevTools do usuário: a chamada `GET /api/widget/{chave}/config?product=vetorleads` partiu do próprio site (`Sec-Fetch-Site: same-origin`, Referer `https://vetorleads.com.br/`) **sem header `Origin`**. O middleware exigia `Origin` e respondia 403 "Domínio não autorizado para este produto".
- Nos testes por curl eu enviava `Origin` manualmente, então não reproduzi o caso.
- Correção no commit local `61c42a2` (`gateway/vetorleads`): sem `Origin` e com `Sec-Fetch-Site: same-origin`, a origem é a do `APP_URL`. Cross-site sem `Origin` continua recusado.
- Testes: 55 passaram (479 asserções) em cópia de trabalho.
- **Não publicado.** Aguardando aprovação para push, gitlink, tag com rollback (`vetorleads:rollback-215abb9`), build e deploy.

### Publicação da correção same-origin (executada)

- Push `215abb9..61c42a2` em `gateway/vetorleads`; gitlink do superprojeto publicado no commit `git log -1` (atualização para `61c42a2`).
- Rollback: `vetorleads:rollback-215abb9`.
- Override (não commitado): `vetorleads:same-origin-61c42a2` nos três serviços.
- Build, `migrate:status` (sem pendências), `up -d --no-deps --no-build` e reload do nginx: concluídos. Três serviços `healthy`.
- Verificação HTTP: GET same-origin sem `Origin` → 200; cross-site sem `Origin` → 403; `Origin` do apex → 200.
- Pendente: confirmar no Firefox do usuário que o chat aparece e que o 403 sumiu.

### Publicação: cor por domínio (executada)

- Push `61c42a2..1c2e70d` em `gateway/vetorleads`; gitlink do superprojeto atualizado para `1c2e70d` e publicado.
- Rollback: `vetorleads:rollback-61c42a2`.
- Override (não commitado): `vetorleads:domain-colors-1c2e70d` nos três serviços.
- Build, `migrate:status` (sem pendências), `up` sem dependências e reload do nginx: concluídos. Três serviços `healthy`.
- Verificação HTTP: home 200; configuração pública com Origin do apex retorna cor do produto; GET same-origin 200.
- Pendente: confirmar no navegador o seletor no painel e a cor por domínio no widget.

### Homologação final: cor por domínio (confirmada pelo usuário)

- Causa do campo que não respondia: `maxLength=7` com valor `#2563eb` preenchido bloqueava a digitação; corrigido em `abc2e4a`.
- Confirmado pelo usuário no navegador após o deploy `vetorleads:hex-input-fix-abc2e4a`: o seletor e o campo hexadecimal funcionam e a cor por domínio é aplicada.
