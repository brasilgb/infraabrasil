# Auditoria e desenho VETOR-CHAT-12

## Antes da edição

Foram lidos `correio.md`, `executed.md`, o relatório CHAT-10.1 em `gateway/vetoros/executed.md`, o inventário e o relatório CHAT-11. Cópias dos relatórios estão nesta pasta. O `initial-state.json` preserva os hashes da solicitação e o estado local dos repositórios antes das alterações. A aplicação já continha as mudanças locais do CHAT-11; elas foram ampliadas, não descartadas. O ponteiro preexistente do VetorOS e suas mudanças do widget foram preservados.

Serviços auditados: `SupportAutomation`, `TenantWhatsapp`, `WahaService`, `AutomationEventPublisher`, `TenantContext`; controllers da Central, contatos recebidos, identificação WebChat e webhook WAHA; modelos de conversa, mensagens, atividades, regras, sessão e estado operacional; migrations e interfaces das duas etapas anteriores.

## Mesmo motor, históricos existentes

`SupportAutomation::process` recebe `WebChatMessage` ou `LeadActivity`. Matching, prioridade, condições de horário, qualificação, memória, opt-out e handoff pertencem a esse mesmo motor. O adaptador `SupportWhatsappConversation` apenas resolve o contexto e registra a entrada autenticada.

`WebConversation` foi ampliado para ser contexto de ambos os canais. Para um WhatsApp já ligado ao CRM, a fonte do histórico continua `LeadActivity`; para um contato espontâneo novo, o histórico usa `WebChatMessage` já existente. A origem do histórico fica estável mesmo após qualificação e vínculo ao CRM: não são copiados os corpos para uma segunda tabela. A Central e os Contatos recebidos combinam as fontes e evitam listar duas vezes o mesmo contato qualificado. Endpoints antigos com chave numérica do lead continuam válidos; novos contatos usam chave `wa:UUID`.

O contexto guarda apenas dados da própria conversa ou do contato associado no mesmo tenant: nome, empresa, telefone, interesse, assunto e etapa. Informações válidas são reaproveitadas; o telefone autenticado do WhatsApp não é perguntado novamente. Intenção de suporte não cria oportunidade. O produto da regra é validado no catálogo do tenant. Acompanhamento comercial reaproveita o lead e suas atividades. A transferência de responsável usa a Central existente e só aceita membros autorizados.

## Identidade e idempotência

O webhook mantém HMAC SHA-512, sessão provisionada, validação de tenant ativo e proibição de `tenant_id` no payload. O tenant é resolvido exclusivamente pelo registro servidor/sessão. A sessão usa trava de cache existente e a linha de conexão usa lock transacional no MariaDB, inclusive quando o cache de teste é local a um processo. O número é normalizado e a identidade de entrada é única por conexão/ID do provedor. Ambiguidade de contato não escolhe um lead arbitrário. Mensagens outbound e ACKs não entram no motor.

A decisão é única por tipo de origem/ID da mensagem. A transação registra contexto, decisão e outbox; não chama WAHA. Reprocessar a entrada não cria outra decisão, tarefa ou oportunidade. Os testes incluem números e IDs iguais em tenants diferentes, acesso cruzado negado e validação dos produtos do tenant. Eventos existentes continuam usando identificadores de conversa, sem acrescentar o telefone como chave externa de evento.

## Entrega externa e recuperação

`SupportAutomationDelivery` e `support:deliver --limit=20` usam o agendador existente. Não há novo container, daemon de produto ou motor paralelo. Cada item é resposta a uma entrada, não campanha. Limite de dez tentativas de envio por minuto do tenant e vinte itens por passagem; sem disparo em massa.

A autorização é conferida novamente antes do envio: sessão/tenant, provisionamento, contato, opt-out, situação, responsável, habilitação global e habilitação/homologação do tenant. A decisão e o envio externo são separados. A claim `sending` é persistida antes da chamada; entrega e tomada humana usam a mesma linha de conversa como trava.

Falha no preflight somente de leitura pode ser repetida com backoff, até cinco tentativas. Sessão reconectada permite nova tentativa segura. Se a chamada de envio falha ou não devolve ID, o item fica `uncertain`: não há repetição automática. Uma claim abandonada por mais de cinco minutos também fica incerta. Corpo e decisão permanecem disponíveis. Owner/admin pode cancelar ou reconciliar usando ID e texto de uma mensagem outbound realmente registrada na mesma conversa. Não é permitido associar mensagem de outro tenant/conversa, ou forçar reenvio de uma entrega incerta. Falhas esgotadas anteriores ao envio podem ser retomadas manualmente.

Essa separação evita afirmar garantia de exactly-once do provedor: WAHA não foi tratado como se possuísse uma chave idempotente que o contrato atual do projeto não oferece.

## Ativação

O padrão de `SUPPORT_AUTOMATION_WHATSAPP_DELIVERY_ENABLED` é falso. Além dessa trava do servidor e da habilitação WAHA existente, cada tenant precisa ter sessão provisionada, homologação atestada e ativação explícita com a confirmação `ATIVAR WHATSAPP`. Regras compartilhadas usam `channel=both`, sem duplicação. Desativar o tenant cancela respostas automáticas pendentes. A ativação não reenvia automaticamente o backlog de quando o canal estava desabilitado.

No ambiente de navegador, esses parâmetros foram habilitados exclusivamente em `backups/chat12/app/.env`, apontando para um WAHA simulado em `127.0.0.1:8015`. A configuração operacional não foi habilitada ou publicada. O simulador é um processo temporário de teste; não instala nem provisiona um serviço de infraestrutura.
