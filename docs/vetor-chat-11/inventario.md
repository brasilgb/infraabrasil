# Auditoria VETOR-CHAT-11

A implementação evolui o atendimento existente, sem novo transporte de mensagens.

| Área | Implementação encontrada e reaproveitada |
| --- | --- |
| Widget do VetorOS | `gateway/vetoros/resources/js/pages/site/components/web-chat.tsx`, polling e proxy da API existente. Acrescentados rótulo de robô e contagem de respostas automáticas não lidas. |
| Entrada WebChat | `Api/WebChatController`: token de serviço, token privado do visitante, mensagens, expiração, encerramento, identificação progressiva. |
| Tenant do site | `VerifyWebChatServiceToken`: tenant vindo de `services.web_chat.tenant_id`, nunca do payload do visitante. `BelongsToTenant` restringe consultas e bindings. |
| Histórico e atendimento humano | `WebChatInboxController`, `SupportInboxController`, `WebChatMessage`, `WebChatEvent` e `SupportThreadState`. Reaproveitados os endpoints e componentes de atendimento. |
| CRM | `identifyContact` extraído do fluxo já existente: normalização brasileira, deduplicação por telefone/e-mail, lock do tenant, ambiguidade encaminhada para revisão. Suporte sem contato correspondente não cria oportunidade. |
| WhatsApp/WAHA | `TenantWhatsappEventController` valida HMAC SHA-512 e sessão provisionada, entra no contexto do tenant, deduplica por ID do provedor e ignora contato ambíguo/desconhecido. Resposta manual passa por `LeadWhatsappService`. A Central preserva ambos os canais. |
| Eventos | `AutomationEventPublisher` e `DeliverAutomationEvent` preservados. As mensagens do robô ficam no histórico WebChat e nas decisões locais; não são reenviadas como eventos de entrada. |
| Permissões | Rotas de regras sob `auth`, `verified`, `commercial:tenant` e `TenantWhatsappAdmin`: owner/admin do tenant; membros operam a Central, mas não administram regras. |

## Configuração existente

O compose conecta a aplicação ao serviço WAHA existente e injeta token/tenant do WebChat e habilitação WAHA por tenant. A inspeção de presença das configurações está em `configuration-audit.json`, sem segredos ou números de telefone. Não foram testadas sessões reais, consultado conteúdo real de conversas ou disparadas mensagens. Os testes da integração usam fakes HTTP.

## Base de conhecimento verificada no código

`gateway/vetoros/routes/app.php` contém clientes e equipamentos (linhas 78–85), ordens (90), orçamentos (108), documentos/configuração fiscal (109–119), peças (154), fornecedores/compras (158–163), vendas (164), despesas e contas a pagar (168–169), caixa (184 em diante) e rotas de gráficos do dashboard (antes de 78). Os controllers correspondentes existem. A base usa descrições gerais dessas rotinas; emissão fiscal depende de configuração e credenciamento. Planos, preços, adequação por segmento e condições comerciais ficam para confirmação da equipe.

## Ambiente e verificações

Aplicação de homologação em `backups/chat11/app`, dependências PHP copiadas do ambiente nativo anterior e dependências Node reutilizadas. Testes em SQLite em memória; navegador em SQLite exclusivo `backups/chat11/ui.sqlite`, sem banco operacional. Nenhum container novo ou serviço pago. O roteiro `browser.cjs` restringe o navegador à origem local. `changed-files.json` identifica os 16 arquivos da aplicação e sua paridade com a cópia testada; `vetoros-widget.diff` preserva a alteração pontual do widget.
