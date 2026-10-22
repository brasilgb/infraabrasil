# Execução — VETORCHAT-SAAS-04

Data: 21/10/2026. Resultado: implementação local do widget multitenant por produto e integração com canais existentes; ativação pública, auditoria da VPS e homologação operacional continuam pendentes.

## Comparação do correio

- Instrução anterior: VETORCHAT-SAAS-03, preservada em `docs/vetorchat-saas-04/executed-anterior.md` (SHA-256 `90074ed78d38ebb0810d4f21cb00ef20855c024b5f5b12cbd2781f2368681d3c`).
- Instrução atual: VETORCHAT-SAAS-04. SHA-256 `762a0d9b557a6e54820585a24be6ffbb90cba0beb128cb6c561d7ad31bbf07f7`.
- Cópia da instrução e diff comparativo: `docs/vetorchat-saas-04/correio.md` e `docs/vetorchat-saas-04/correio-comparison.diff`.

## Arquitetura e funcionamento

- VetorLeads mantém o widget, os produtos, conversas e atendimento. `tenant_chat_widgets` fornece uma chave pública por tenant; `chat_products` mantém produtos separados dentro desse tenant. O endpoint resolve o tenant pela chave no servidor.
- O produto é enviado como `data-product`. O middleware confere produto ativo e origem HTTPS exata cadastrada nele. Para instalações sem produto explícito, `generic` permanece disponível sob as origens configuradas no widget.
- As conversas do widget recebem produto e tenant no servidor. O token do visitante é separado por produto, evitando que o mesmo navegador leia histórico de outro produto. A caixa de entrada permite filtrar produto, canal e status.
- VetorOS, VetorLeads, VetorPet e ABrasil Sistemas agora têm instalação configurável por `VETORCHAT_WIDGET_KEY` e `VETORCHAT_SCRIPT_URL`, usando respectivamente `vetoros`, `vetorleads`, `vetorpet` e `abrasil`. O snippet só é renderizado em páginas públicas e só com chave de 48 caracteres e URL HTTPS. A chave continua vazia nos exemplos de ambiente.
- Cada tenant mantém sua conexão e sessão WAHA. O `WahaService` existente é reutilizado; o webhook é autenticado por HMAC, vinculado à sessão/tenant, deduplicado por recibo e processa ACKs de entrega. A resposta WhatsApp iniciada a partir de conversa web mantém o produto original e exige consentimento registrado.
- Mensagens WhatsApp iniciadas diretamente pelo contato não contêm uma origem de produto verificável; elas entram como `generic`. Respostas determinísticas e regras podem ser vinculadas a produtos. Não foi identificado mecanismo de IA operacional conectado a esse fluxo; respostas generativas permanecem pendentes.

## Alterações e validação

- No VetorLeads já havia uma implementação extensa e não commitada no início desta continuação. Ela foi revisada e preservada; inclui widget público, catálogo/gestão de produtos, regras de automação por produto, inbox, ponte webchat–WhatsApp, auditoria e migrations. Foram acrescentadas as instalações dos quatro produtos, testes de renderização pública nos três submódulos sem teste equivalente e ajuste do teste institucional para afirmar `data-product="abrasil"`.
- `gateway/abrasilsistemas`: 10 testes passaram, 40 assertions.
- `gateway/vetorleads`: 24 testes passaram, 231 assertions, com cache de rotas direcionado a `/tmp` para não tocar no cache local preexistente.
- `gateway/vetoros`: 2 testes passaram, 5 assertions, com avisos do ambiente de testes.
- `gateway/vetorpet`: teste criado, mas não executado porque `vendor/autoload.php` não existe nesse checkout.
- PHP lint dos arquivos centrais do VetorLeads passou. Não foi feita homologação por navegador nesta etapa.

## Pendências de ativação

- `/opt/infra-abrasil` não está disponível neste ambiente; não foi possível auditar WAHA, containers, variáveis ou configuração em execução na VPS.
- Cadastrar e ativar o tenant ABrasil e os quatro produtos no VetorLeads; configurar domínios reais, operadores, mensagens, horários e regras. A rota de preparação do ecossistema cria os produtos inativos, sem domínios.
- Copiar a mesma chave pública ABrasil para o ambiente dos quatro sites e manter o slug correspondente. A chave comum não foi inserida em `.env`, no código ou nos exemplos.
- Confirmar que a sessão WAHA compartilhada do tenant ABrasil está provisionada e homologada. Para tenants externos, confirmar sessões próprias e isolamento no ambiente MariaDB/WAHA real.
- Aplicar migrations e publicar código/assets somente em janela autorizada; testar recebimento e envio com contatos consentidos e validar o desligamento público.

## Restrições e estado do Git

- Não houve deploy, migration de produção, alteração de sessão WAHA, envio de mensagem real, commit ou push.
- O repositório raiz está em rebase interativo com conflito preexistente em `gateway/vetorpet`; esse estado e as demais alterações locais foram preservados.
- Nenhum container Docker foi iniciado ou alterado. Testes usaram dados sintéticos e não enviaram chamadas a WAHA real.
