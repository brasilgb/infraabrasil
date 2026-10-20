# VETOR-CHAT-12.7 — Resultado da execução

## Comparação com a solicitação anterior

`correio.md` contém o CHAT-12.7 e é diferente do CHAT-12.6 arquivado: SHA-256 atual `7f68bd0d74278177747dbfef18d0a33c45d257db4c9be3233c110a9dd3a2bbed`; CHAT-12.6 `ab0c56eb57ff48433f96a81379d1c3b27fea1576bd9d6fc2200b384d10296729`. O relatório e a solicitação anteriores foram preservados em `docs/vetor-chat-12/chat126-executed.md` e `docs/vetor-chat-12/chat127-correio.md`.

## Implementação local

Foi criado o Laboratório de Atendimento em Automação de Atendimento, protegido pelo mesmo grupo administrativo de tenant (`auth`, `verified`, `commercial:tenant` e `TenantWhatsappAdmin`). O administrador escolhe WebChat ou WhatsApp simulado e seleciona regras do próprio tenant, ativas ou rascunhos. O motor usa a correspondência e as regras de agenda do serviço `SupportAutomation`; a simulação mantém mensagens e decisões entre turnos.

Conversas e mensagens do laboratório usam `support_sandbox_conversations` e `support_sandbox_messages`, tabelas próprias com `tenant_id`, escopo de tenant e chaves próprias. Elas não usam `web_conversations`, CRM, histórico operacional, decisões de produção, filas, WAHA, jobs ou outbox. O laboratório permite qualificação simulada, prévia de contato/oportunidade, assumir como operador fictício, resposta humana interna, retomar automação, reiniciar e encerrar. A tela informa que a prévia não grava CRM.

Arquivos desta etapa:

- `gateway/vetorleads/app/Http/Controllers/SupportSandboxController.php`
- `gateway/vetorleads/app/Models/SupportSandboxConversation.php`
- `gateway/vetorleads/app/Models/SupportSandboxMessage.php`
- `gateway/vetorleads/app/Services/SupportAutomation.php` — matcher reutilizável para o conjunto explicitamente selecionado
- `gateway/vetorleads/database/migrations/2026_10_19_200000_create_support_sandbox_tables.php`
- `gateway/vetorleads/routes/settings.php`
- `gateway/vetorleads/resources/js/pages/support-automation/lab.tsx`
- `gateway/vetorleads/resources/js/pages/support-automation/index.tsx`
- `gateway/vetorleads/tests/Feature/SupportSandboxTest.php`
- `gateway/vetorleads/config/support_automation.php` — removeu a promessa de Pix exclusivo da resposta de planos
- `gateway/vetoros/resources/js/pages/site/components/faq.tsx` — cópia local revisada para não afirmar modalidade de pagamento não confirmada

## Regras e travas

A resposta de planos continua informando o teste de 14 dias sem cartão, mas encaminha a confirmação das condições vigentes ao site/equipe. A FAQ local não promete Pix. Não houve revisão individual documentada das 14 regras; a mudança de respostas se limita à divergência identificada. Nenhuma regra foi ativada e a regra fora do expediente permanece inativa. Não houve publicação, alteração de produção, mensagem real ou modificação de registros comerciais. O widget público continua no fluxo operacional existente.

## Validação executada

- Suíte PHP completa, SQLite em memória com proteção de testes: **1.313 testes e 13.881 asserções aprovados**.
- Testes do laboratório: **3 aprovados**, incluindo jornada multimensagem com rascunho, qualificação e operador, ausência de escrita em CRM/conversa/outbox/decisões operacionais, autorização e rejeição de regra de outro tenant.
- `npm run types:check` (VetorLeads): aprovado.
- `npm run lint:check` (VetorLeads): aprovado.
- PHPStan (VetorLeads): aprovado, zero erros.
- `npm run build` (VetorLeads): aprovado. Os artefatos locais gerados foram removidos/restaurados depois da verificação.
- `php -l` nos novos arquivos PHP: aprovado.
- VetorOS: `npm run types` e `npm run build` não executaram porque as dependências locais (`tsc` e `vite`) não estão instaladas. `npm run test:js` também falhou antes de carregar os testes: Node 20 não reconheceu os arquivos TypeScript diretamente.
- Chromium desktop/mobile e capturas visuais: não executados.

## Pendências antes de considerar o piloto completo

O CHAT-12.7 pede também sessão temporária do servidor, restrita a identidade de teste, para o widget real VetorOS. Essa ponte de homologação do widget (emissão, expiração e revogação de credencial) não foi implementada nesta etapa; por isso não se deve expor rascunhos no widget nem considerar concluída a homologação ponta a ponta. Também faltam revisão registrada das 14 respostas, validação do build VetorOS com dependências disponíveis e verificação visual em Chromium desktop/celular.

Antes de publicação: instalar dependências reproduzíveis do VetorOS, completar a ponte autenticada e temporária do widget com testes de token expirado/revogado e ausência de chamadas operacionais, revisar cada regra, capturar a interface em Chromium nos dois tamanhos e repetir as validações. Só então avaliar publicação e migrações conforme o procedimento normal. Esta execução não publicou nada.

## Estado do workspace

As alterações são locais e permanecem sem commit. Arquivos previamente modificados do VetorLeads/VetorOS e pastas de evidências das etapas anteriores foram preservados. A compilação produziu temporariamente artefatos em `gateway/vetorleads/public/build`; os arquivos rastreados foram restaurados e os artefatos novos removidos para não poluir o diff.
