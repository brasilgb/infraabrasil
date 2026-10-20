# VETOR-CHAT-12.2 — Configuração de regras e homologação controlada

Executado em **19/10/2026 UTC**.

**Resultado: 14 respostas cadastradas como rascunhos inativos no tenant Anderson Brasil; atualização direcionada do VetorLeads concluída; WhatsApp continua bloqueado globalmente.** A homologação completa dos fluxos não foi declarada: a suíte desta mudança e a revisão visual automatizada não puderam ser executadas neste ambiente.

## Comparação e preservação

O `correio.md` mudou de **VETOR-CHAT-12.1 — Publicação e homologação operacional** para **VETOR-CHAT-12.2 — Configuração de Regras e Homologação Controlada**.

- SHA-256 do correio 12.1, registrado no relatório preservado: `7625b2d9927b6a37fbcb0d14e3ea94b24c3852c8d0e3ffecf4396a9cd5ea4678`.
- SHA-256 do correio 12.2: `2829878064158eee8e8c35bb76f19193101e50eabb0933afb8689ebb564b3107`.
- O relatório anterior foi preservado em [chat121-executed.md](docs/vetor-chat-12/chat121-executed.md). Foram consultados também os relatórios CHAT-11 e CHAT-12.

## Alterações executadas

- Preparada biblioteca de **14 respostas editáveis**, abrangendo boas-vindas, produto, assistência, clientes/equipamentos, OS, orçamento, peças/estoque, vendas/financeiro, fiscal, planos, demonstração, suporte, fora de expediente e transferência humana. O conteúdo segue as informações verificadas no VetorOS e evita fixar preços ou prometer reserva automática de demonstração.
- As 14 regras foram cadastradas no tenant **Anderson Brasil (ID 1)**, para os canais `both`, todas com `active=false`. Não havia regras existentes para substituir. Cada criação recebeu registro de auditoria `draft_created`.
- A biblioteca administrativa faz cadastro idempotente: regras já existentes com o mesmo nome e canal são preservadas. A prévia permite escolher regra inativa e informa que não grava conversa, lead, mensagem ou outbox.
- A solicitação explícita por atendente humano pausa a automação e gera resposta de encaminhamento, usando a regra ativa de handoff quando existir ou uma resposta segura padrão.
- A tela de administração apresenta checklist informativo de prontidão, canais, horários, operadores, sessão WAHA, homologação, autorização e trava global. O checklist não habilita a automação.
- O relatório anterior foi preservado no caminho acima.

## Publicação direcionada e estado operacional

- Build do serviço `vetorleads` concluído; publicação limitada a `vetorleads`, `vetorleads-worker` e `vetorleads-scheduler`. Nenhum outro aplicativo/serviço foi atualizado nesta execução.
- Os três serviços foram verificados como **running/healthy** após a atualização.
- Verificação em runtime: tenant **Anderson Brasil**, **14 regras**, **0 regras ativas**; `support_automation.whatsapp_delivery_enabled=false`.
- A sessão WAHA existente foi preservada. Nenhuma mensagem real foi enviada, nenhuma regra produtiva foi ativada e nenhum histórico foi apagado.
- Nenhuma migration foi necessária nesta atualização.

## Validação e limites

- Build Vite de produção: **passou** (2.413 módulos transformados).
- `php -l`: passou nos arquivos alterados de controller, serviço, configuração e teste.
- A suíte direcionada `SupportAutomationTest.php` e `SupportMultichannelTest.php` foi tentada, mas não iniciou: o workspace não contém `vendor`; a imagem disponível foi construída com `composer install --no-dev`, sem PHPUnit/Pest. A tentativa via bind mount também não encontrou `vendor`.
- `npm run types:check` foi tentado no workspace, mas não iniciou porque `node_modules/tsc` não existe. O build Vite passou, mas isso não substitui a checagem dedicada de tipos.
- Não foi realizada revisão visual automatizada nesta rodada. Também não foi repetido webhook/HMAC, processamento da outbox, journeys sintéticas ou teste de isolamento; a execução 12.1 registrou HMAC inválido `401`, evento sintético válido `201`, replay idempotente `200`, zero regras ativas e zero itens na outbox naquele momento. Esses registros prévios não comprovam a homologação das novas respostas.
- Há cobertura nova adicionada para criação idempotente de rascunhos e prévia isolada, mas ela aguarda execução numa instalação com dependências de desenvolvimento.

## Pendências antes de ativar

O checklist operacional permanece incompleto: regras aguardam revisão humana; horário e fuso de atendimento fora do expediente não foram definidos; homologação e autorização de ativação não foram registradas. Validar as jornadas WebChat e WhatsApp em identidade/ambiente de teste, incluindo qualificação, CRM, transferência humana, retorno ao automático, opt-out, histórico, outbox, duplicidade, suspensão e recuperação. Repetir regressão e revisão visual com as dependências de desenvolvimento disponíveis.

`SUPPORT_AUTOMATION_WHATSAPP_DELIVERY_ENABLED=false`; tenant sem ativação; **nenhum envio externo realizado**. Não houve commit nem push. As alterações locais anteriores do VetorLeads e VetorOS foram preservadas.
