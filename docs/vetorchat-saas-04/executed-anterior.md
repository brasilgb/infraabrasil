# Execução — VETORCHAT-SAAS-03

Data: 21/10/2026. Resultado: preparação local e validação sintética da integração institucional; **ativação e homologação na VPS pendentes**.

## Comparação com a instrução anterior

O `correio.md` mudou. A referência da última execução é `docs/vetorchat-saas-02/correio.md`, identificada pelo relatório anterior, e não `correio.md.save` (que contém uma tarefa mais antiga).

- Anterior: **VETORCHAT-SAAS-02**, implementação da arquitetura SaaS multitenant. SHA-256: `60844e32f54a890cea0e66fff3a29cacd02fcedd32dfe6d0305c1af9c0a0db64`.
- Atual: **VETORCHAT-SAAS-03**, preparação da instalação institucional, validação operacional e documentação para ativação controlada. SHA-256: `d5c400effde7f1eab146617433312060a9afcff45300280c9ef6245ada7e2116`.
- A arquitetura permanece: VetorLeads centraliza o serviço; ABrasil instala o widget como cliente.

A instrução recebida, o diff e o relatório anterior foram preservados em `docs/vetorchat-saas-03/`.

## Auditoria antes das alterações

Foram inspecionados os controllers, middleware, modelos, migration, rotas, widget JavaScript, tela de configuração, fluxo de contatos/CRM e template institucional existentes.

- O tenant é resolvido pela sessão e memberships no painel. A chave pública resolve `TenantChatWidget` no servidor; o navegador não escolhe o tenant. Não foi fixado um ID global para ABrasil.
- `tenant_chat_widgets` persiste chave pública aleatória de 48 caracteres, ativação, configurações JSON e evidências de conexão por domínio. A configuração possui escopo por tenant.
- A chave já era gerada automaticamente, mas faltava um fluxo administrativo de rotação. Foi acrescentado.
- A instalação condicional já existia no template da ABrasil, com `VETORCHAT_WIDGET_KEY` e `VETORCHAT_SCRIPT_URL`. Estava ausente na página `company-websites`.
- O loader utiliza Shadow DOM e deduplicação de montagem; a posição padrão colidia com o WhatsApp institucional. A camada padrão também podia cobrir o aviso de cookies.
- A origem exige HTTPS e correspondência exata com os domínios autorizados. CORS é específico do widget, com preflight e sem uso de cookies administrativos; há limites distintos para leitura, abertura, mensagens, identificação e heartbeat.
- A chave pública não autentica ações administrativas. Mensagens utilizam credencial própria do visitante e identificadores idempotentes.
- Contatos recebidos permanecem em Atendimento/Contatos recebidos. Abrir conversa ou identificar suporte não cria lead comercial automaticamente. Identificação comercial pode criar/associar lead, preservando origem, interesse e histórico; correspondências ambíguas exigem revisão. Não foi criado outro backend na ABrasil.

## Alterações desta execução

No submódulo `gateway/vetorleads`:

1. `app/Http/Controllers/Settings/ChatWidgetController.php`: rotação com confirmação explícita; troca somente a chave do tenant atual e limpa heartbeats anteriores, preservando configuração, ativação e histórico.
2. `routes/settings.php`: endpoint POST de rotação sob autenticação, e-mail verificado, tenant ativo, permissão owner/admin, CSRF e throttle.
3. `resources/js/pages/settings/chat-widget.tsx`: confirmação e botão de rotação; aviso de atualização dos snippets e início de nova sessão para visitantes.
4. `public/widget.js`: suporte opcional a `data-position="left"` e `data-z-index`, validado como inteiro não negativo até 2147483647. Instalações que omitem esses atributos mantêm o comportamento existente.
5. `tests/Feature/TenantChatWidgetTest.php`: cobertura de autorização, confirmação, revogação da chave antiga, nova chave funcional, limpeza de conexões e preservação do histórico/tenant vizinho.

No submódulo `gateway/abrasilsistemas`:

6. `resources/views/app.blade.php`: inclui `company-websites`; instala à esquerda e na camada 50 para coexistir com WhatsApp e com o aviso de cookies, cuja camada é 60.
7. `tests/Feature/InstitutionalChatWidgetTest.php`: verifica páginas públicas, configuração ausente/inválida, um único snippet e ausência de instalação em login/configurações administrativas.

Na raiz: atualização de `executed.md` e criação das evidências em `docs/vetorchat-saas-03/`. Não foram alterados `.env`, serviços Docker, configurações da VPS ou outros módulos. Alterações preexistentes e o conflito Git preexistente em `gateway/vetorpet` foram preservados.

## Testes e resultados

Os testes automatizados antecederam os testes de navegador.

| Verificação | Resultado |
| --- | --- |
| Suíte completa VetorLeads, SQLite em memória | 1.398 aprovados; 14.703 assertions; 82,0 s |
| Suíte completa ABrasil, SQLite em memória | 255 aprovados; 1.679 assertions; 7,6 s |
| TypeScript VetorLeads | Aprovado, sem diagnósticos |
| ESLint da tela alterada | Aprovado |
| Prettier da tela e widget | Aprovado |
| Pint dos arquivos PHP afetados | Aprovado |
| `node --check public/widget.js` | Aprovado |
| Build Vite em `/tmp`, sem sobrescrever build existente | Aprovado; 13,84 s |

O último ajuste de camada de exibição foi validado novamente com sintaxe/formatação do widget, testes institucionais direcionados e navegador. Os logs individuais ficam junto das evidências.

A primeira tentativa de testes VetorLeads encontrou 14 falhas HTTP 404 porque havia cache de rotas preexistente, anterior às rotas do widget. A suíte completa passou usando `APP_ROUTES_CACHE=/tmp/vetorchat-saas03-no-routes.php`, sem remover ou substituir esse cache. A atualização de caches na VPS permanece uma etapa futura.

No navegador, a primeira tentativa com HTML institucional precisou corrigir URLs de assets no encaminhamento local de teste. A falha era do harness; após o ajuste o frontend real carregou. Não foi necessária repetição indefinida de uma ferramenta indisponível.

## Evidências do fluxo e instalação

Playwright/Chromium utilizou dois tenants sintéticos, backend Laravel local e bancos separados `/tmp/vetorchat_saas03_test.sqlite` e `/tmp/abrasil_saas03_test.sqlite`. O domínio HTTPS do visitante e o domínio de script foram encaminhados pelo harness a servidores PHP locais. Portanto, os testes não homologam DNS, TLS, Nginx, rede ou configuração reais da VPS.

Foram verificados no navegador:

- Tela administrativa desktop/mobile; envio do visitante e resposta pelo painel VetorLeads retornando ao widget.
- HTML e frontend reais da ABrasil servidos localmente, com chave sintética e instalação repetida para exercitar deduplicação.
- Histórico recuperado após reload e navegação entre páginas, sem duplicação da mensagem.
- Separação entre chat e WhatsApp no frontend institucional desktop/mobile; controles de consentimento acessíveis antes da escolha de cookies.
- Segundo tenant sem receber histórico/resposta do primeiro; resposta do segundo isolada do primeiro.
- Widget dentro da largura de 390 pixels; encerramento e remoção de widget/timers.
- Rotação pela interface exige confirmação e apresenta novo snippet.
- Ausência de erros JavaScript.

Arquivos de evidência em `docs/vetorchat-saas-03/`: `browser-results.json`, `browser-run.log`, `browser-check.mjs`, `settings-desktop.png`, `settings-mobile.png`, `widget-desktop.png`, `widget-mobile-a.png`, `widget-mobile-b.png`, `cookie-consent.png`, logs Pest e build Vite. As capturas exibem exclusivamente dados sintéticos. O script de navegador registra os caminhos temporários usados nesta execução.

## Snippet preparado

O modelo está em `docs/vetorchat-saas-03/installation-snippet.html`:

```html
<script src="https://vetorleads.com.br/widget.js"
        data-widget-key="CHAVE_PUBLICA_DE_48_CARACTERES"
        data-position="left" data-z-index="50" async></script>
```

O placeholder é intencionalmente inválido. A chave real deve ser copiada do painel do tenant correto. Não utilizar chave sintética, ID de tenant, token administrativo ou credenciais de serviço. O domínio VetorLeads corresponde à configuração Nginx do repositório; disponibilidade pública atual não foi homologada.

No projeto institucional, o template já monta o script quando a configuração é válida; basta preencher a configuração no ambiente de destino. Não é necessário colar um segundo snippet no template.

## Procedimento de ativação do primeiro tenant

1. Em uma janela de implantação autorizada, confirmar backup/restauração, versões dos dois projetos, assets, permissões e migration pendente `2026_10_21_140000_create_tenant_chat_widgets.php`. Nesta execução nenhuma migration de produção foi aplicada.
2. No VetorLeads, autenticar um owner/admin com e-mail verificado e selecionar explicitamente o tenant ABrasil Sistemas. Confirmar identidade, membros e operador responsável; esta execução não consultou nem confirmou o tenant real na VPS.
3. Em Configurações → Canais → Chat do Site, configurar marca, privacidade `https://abrasilsistemas.com.br/politica-de-privacidade`, mensagens e atendentes. Confirmar dias, fuso e horário reais; os valores padrão não representam uma escala de atendimento homologada.
4. Cadastrar `https://abrasilsistemas.com.br`. Adicionar `https://www.abrasilsistemas.com.br` somente se o widget efetivamente executar nessa origem; redirecionar para o domínio canônico é preferível. As origens são independentes.
5. Copiar a chave pública gerada e preparar no ambiente institucional `VETORCHAT_WIDGET_KEY=<chave real>` e `VETORCHAT_SCRIPT_URL=https://vetorleads.com.br/widget.js`. Ajustar caches somente na implantação autorizada. Não inserir segredo administrativo no navegador.
6. Conferir que a VPS entrega `/widget.js` atualizado e as rotas `/api/widget/...`; revisar proxy HTTPS/APP_URL, origem/CORS, cache e política CSP caso configurada. Caches antigos podem produzir 404 ou snippets com URL incorreta.
7. Na ativação controlada, salvar o widget ativo, abrir o domínio real em desktop/mobile, verificar instalação e heartbeat recente. Distinguir script detectado de conexão efetiva.
8. Com contatos de teste autorizados, enviar mensagem, localizar na caixa de entrada do tenant ABrasil, responder como atendente, recarregar, encerrar e tentar acesso por outro tenant. Validar identificação comercial/deduplicação e ausência de mistura com prospecção.
9. Testar desligamento pelo painel; confirmar bloqueio público e preservação do histórico. Só então registrar homologação real e liberar uso operacional.

O mesmo procedimento se aplica a novos tenants, utilizando suas próprias configurações, origens, operadores e chaves, sem IDs globais nem alteração de código. A posição/camada são opcionais por site.

## Rotação e recuperação

O owner/admin confirma “Renovar chave pública” no painel. A chave anterior deixa de resolver o widget imediatamente; os sites devem receber a chave nova e seus caches devem ser atualizados. O histórico permanece no painel. Como o armazenamento do visitante é separado por chave, ele inicia nova sessão após a troca; não se promete continuidade automática da sessão antiga. Nenhuma chave real foi rotacionada nesta execução.

## Pendências e riscos

- Não houve acesso, ativação ou homologação real na VPS. Falta confirmar tenant ABrasil real, operadores, horário, domínio/TLS, schema/migration, build publicado, caches e configuração institucional.
- O código permanece no working tree, com mudanças anteriores ainda sem commit. A publicação precisa considerar esse conjunto e o conflito Git preexistente, sem sobrescrever trabalho.
- Rotação interrompe instalações antigas até a substituição da chave; deve ocorrer em janela controlada. Desativar o widget é o desligamento imediato disponível sem apagar conversas.
- Origem/CORS protegem o uso no navegador; clientes HTTP podem forjar Origin. A chave é pública e os limites de uso continuam necessários. Rate limiting e concorrência em MariaDB/proxy reais precisam de confirmação na VPS; SQLite não reproduz todas as condições de produção.
- A integração comercial foi validada para criação/associação de lead existente. Não cria oportunidade apenas por abrir o chat e não confirma integração comercial externa nem envio de WhatsApp.
- Os testes de navegador usaram encaminhamento HTTPS simulado, fixture de autenticação exclusiva do harness e banco local; não equivalem a teste de login, sessão ou transporte reais da VPS.

Não foram executados deploy, commit, push, migrations de produção, ativação pública ou envio de mensagens a contatos reais. Nenhum container Docker local foi criado, iniciado ou modificado.
