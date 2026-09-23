Corrija exclusivamente a autenticação da integração Laravel → n8n para o envio manual de WhatsApp implementado na etapa anterior.

CONTEXTO CONFIRMADO

O webhook de produção do n8n é:

https://n8n.abrasilsistemas.com.br/webhook/crm/send-whatsapp

O node Webhook está configurado com:

- HTTP Method: POST
- Path: crm/send-whatsapp
- Authentication: Header Auth
- Credential: X-CRM-Token
- Respond: Using "Respond to Webhook" Node

A chamada atual do LeadWhatsappService chega ao n8n, mas retorna HTTP 403 porque o Laravel não envia a autenticação Header Auth.

OBJETIVO

Adicionar suporte seguro ao token/header exigido pelo webhook n8n.

REQUISITOS

1. Não alterar o n8n.
2. Não remover a autenticação do webhook.
3. Não hardcodar token no código.
4. Não colocar token no frontend.
5. Não colocar token em logs.
6. Não alterar o fluxo funcional já implementado.
7. Não criar migration.
8. Não criar LeadActivity adicional.
9. Não alterar endpoints de ACK/status existentes.

CONFIGURAÇÃO

Adicionar em config/services.php, no bloco n8n existente, configuração para:

- nome do header
- token

Sugestão:

'n8n' => [
    'whatsapp_webhook_url' => env('N8N_WHATSAPP_WEBHOOK_URL'),
    'whatsapp_webhook_timeout' => (int) env('N8N_WHATSAPP_WEBHOOK_TIMEOUT', 20),
    'whatsapp_webhook_header' => env('N8N_WHATSAPP_WEBHOOK_HEADER', 'X-CRM-Token'),
    'whatsapp_webhook_token' => env('N8N_WHATSAPP_WEBHOOK_TOKEN'),
],

O nome real do header deve ser confirmado pela configuração/credential existente se for possível inspecioná-la sem revelar o segredo.

No LeadWhatsappService, adicionar o header à chamada HTTP usando Laravel HTTP Client, por exemplo através de withHeaders(), sem expor o valor.

Se o token não estiver configurado, NÃO realizar a chamada ao n8n. Retornar erro amigável de configuração e registrar somente informação segura no log.

INFRAESTRUTURA

O serviço abrasilsistema recebe variáveis através do docker-compose.

Preparar a aplicação para receber:

N8N_WHATSAPP_WEBHOOK_HEADER
N8N_WHATSAPP_WEBHOOK_TOKEN

Não inserir o valor real do token no repositório.

Não modificar automaticamente o .env de produção.

TESTES

Atualizar LeadWhatsappSendTest usando Http::fake() para verificar:

- header correto enviado ao n8n;
- token correto enviado;
- token ausente => n8n não é chamado;
- token/header não aparecem em props Inertia;
- token não aparece nas mensagens de erro;
- fluxo de sucesso continua funcionando;
- payload continua exatamente:
  prospect_id
  nome
  whatsapp
  mensagem
- nenhuma LeadActivity extra é criada.

Executar os testes relacionados, Pint, TypeScript/build se necessário e git diff --check.

IMPORTANTE

Todos os comandos PHP/Artisan devem ser executados em container descartável conforme o procedimento já utilizado. Não executar PHP/Artisan diretamente no host e não modificar o container de produção para testar.

Não fazer deploy.

No relatório final informe exatamente:
1. arquivos alterados;
2. configuração adicionada;
3. nome do header encontrado;
4. como configurar docker-compose;
5. quais variáveis adicionar ao /opt/infra-abrasil/.env;
6. testes executados e resultados;
7. comandos necessários para o deploy, mas não os execute.
