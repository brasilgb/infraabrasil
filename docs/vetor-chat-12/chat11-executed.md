# VETOR-CHAT-11 — Atendimento automatizado do VetorOS

Executado em **19/10/2026 UTC**.

**Resultado: automação do WebChat implementada e homologada localmente, com dados sintéticos.** A integração WAHA existente foi preservada; respostas automáticas no WhatsApp não foram implementadas nesta entrega. Os limites e as pendências estão descritos abaixo. Não houve deploy, push, migration operacional, envio de mensagens reais ou alteração de contatos comerciais reais.

## Comparação do correio

O `correio.md` está diferente da solicitação anteriormente executada, **VETORLEADS-COMERCIAL-01**. A solicitação atual é **VETOR-CHAT-11**:

- SHA-256 anterior, conforme relatório preservado: `d9bd19925dfc575f9850453d48ac6e124517b786fd187b9db0b338f7793e545f`.
- SHA-256 atual: `48f046cf2af428b8c56b7466b0773a0e176bdc879402a7e5201c54d6b4e7fe50`.
- O HEAD da infraestrutura ainda contém PROSPECT-08; por isso a comparação também considerou o último resultado executado, em vez de tratar o HEAD como a última solicitação atendida.

[Relatório anterior preservado](docs/vetor-chat-11/commercial01-executed.md), [solicitação executada](docs/vetor-chat-11/correio.md) e [metadados](docs/vetor-chat-11/execution.json).

## Auditoria e reaproveitamento

O [inventário técnico](docs/vetor-chat-11/inventario.md) identifica widget, API, modelos, histórico, Central de atendimento, associação com CRM, eventos e configuração WAHA existentes.

O WebChat usa token de serviço e token privado do visitante. O tenant vem da configuração do servidor; consultas e bindings usam o escopo existente. Mensagens recebidas/enviadas, histórico, identificação progressiva, encerramento e associação comercial foram preservados. A Central continua reunindo WebChat e WhatsApp.

WAHA mantém o webhook com HMAC, identificação da sessão do tenant, deduplicação por ID do provedor e envio manual pelo serviço existente. A [auditoria de configuração](docs/vetor-chat-11/configuration-audit.json) registra apenas presença de parâmetros, sem revelar segredos. Não foi feita sondagem de sessões ou mensagens reais.

## Implementação local

**Motor de regras:** opt-in por tenant, canal WebChat, prioridade e desempate por ID; boas-vindas, palavras/expressões equivalentes, comparação sem acentos e sem diferença de caixa, fallback, resposta, qualificação e transferência. Intenções explícitas precedem boas-vindas e fallback. As condições de horário permitem dias da semana, fuso e respostas dentro/fora do expediente. Não há IA ou serviço pago.

**Qualificação:** nome → empresa → telefone com DDD → interesse. Validação de tamanho e telefone; cancelamento ou pedido de operador durante a coleta suspende a automação. A identificação reutiliza o fluxo de deduplicação existente, incluindo lock do tenant e revisão manual de contatos ambíguos. Oportunidade nova é criada apenas para assunto comercial; suporte e demais assuntos permanecem contatos recebidos quando não existe correspondência. Origem `WebChat · VetorOS`, nota de interesse e acompanhamento comercial quando aplicável. Não são criados prospects de prospecção ativa, propostas ou vendas. Não há inscrição automática em campanhas ou consentimento presumido para WhatsApp.

**Atendimento humano:** resposta, atribuição ou mudança para andamento suspendem o robô. Há indicador de automação suspensa/liberada e botão **Devolver ao automático**, que remove o responsável e retoma o estado pendente. A coleta é reiniciada no retorno. Os caminhos das duas caixas de atendimento usam a mesma trava de conversa do motor para serializar a tomada humana e a decisão automática.

**Repetições e histórico:** cada mensagem recebida possui no máximo uma decisão; decisão e resposta automática são gravadas na mesma transação. Eventos de robô não são reprocessados como mensagens do visitante. Repetição de uma regra no mesmo ciclo suspende a automação sem reenviar a resposta; a devolução explícita inicia novo ciclo. Decisões guardam resultado, mensagem recebida, resposta e snapshot da regra. Mensagens automáticas ficam identificadas como **Assistente automático**; respostas humanas têm identificação de operador na Central.

**Administração:** área **Automação de Atendimento** no rodapé do menu, junto das configurações. Cadastro/edição, ativação/desativação, exclusão lógica, prioridade, editor de respostas, horário, indicadores por resultado, decisões recentes e auditoria com snapshots antes/depois. Acesso restrito a owner/admin do tenant; membros não administram regras. O simulador de intenção consulta regras ativas sem criar conversa, contato ou mensagem.

**Conhecimento:** onze modelos editáveis de resposta sobre produto, segmentos, clientes/equipamentos, OS, orçamentos/peças, estoque/compras/vendas, caixa/financeiro, fiscal, indicadores, planos/demonstração e suporte. Os temas foram confrontados com rotas/controllers do VetorOS. Não há preços ou promessas específicas de disponibilidade. Condições fiscais e comerciais exigem confirmação da equipe. Os modelos não são inseridos ou ativados automaticamente em produção.

## Arquivos e migration

[Manifesto com hashes e paridade](docs/vetor-chat-11/changed-files.json): **16 arquivos** alterados/criados no VetorLeads. Principais arquivos:

- `app/Services/SupportAutomation.php` e `app/Models/SupportAutomationRule.php`.
- `SupportAutomationController`, integração em `Api/WebChatController`, `SupportInboxController` e `WebChatInboxController`.
- `resources/js/pages/support-automation/index.tsx`, ajustes nas caixas de atendimento, menu e rotas administrativas.
- `config/support_automation.php` e `tests/Feature/SupportAutomationTest.php`.
- Migration **`2026_10_19_180000_create_support_automation_tables.php`**: regras, auditorias, decisões e campos de pausa/coleta/retomada em `web_conversations`. Sem backfill ou regras ativas automáticas.

No VetorOS houve ajuste pontual do widget para identificar robô e contar suas respostas não lidas. [Diff preservado](docs/vetor-chat-11/vetoros-widget.diff). O ponteiro preexistente do submódulo foi preservado. Não foram criados commits ou descartadas alterações anteriores.

## Homologação e evidências

- **Regressão completa: 1.289 testes aprovados, 13.704 assertions, 78,604 s.** [Resultado](docs/vetor-chat-11/all-tests.log).
- **32 testes específicos / 271 assertions**, incluindo os fluxos anteriores de WebChat e Central, qualificação, CRM, suporte sem oportunidade, replay, repetição, tenant, permissões, exclusão lógica, auditoria, horário, cancelamento e retorno ao automático. [Resultado](docs/vetor-chat-11/functional-tests.log).
- **PHPStan nível 7 sem erros**, TypeScript e ESLint aprovados; formatação Pint/Prettier e build Vite aprovados. [PHPStan](docs/vetor-chat-11/phpstan.log), [TypeScript](docs/vetor-chat-11/typescript.log), [ESLint](docs/vetor-chat-11/eslint.log) e [build](docs/vetor-chat-11/build.log).
- **Chromium:** jornada pelo WebChat até identificação no CRM, edição de regra, simulador, resposta humana, suspensão e botão de retorno ao automático. **Seis verificações em 1440 e 390 px**, com HTTP 200, sem erros JavaScript ou transbordamento horizontal do documento. [Resultado](docs/vetor-chat-11/interface/results.json), [roteiro](docs/vetor-chat-11/browser.cjs), [administração celular](docs/vetor-chat-11/interface/390-_settings_support-automation.png) e [Central desktop](docs/vetor-chat-11/interface/1440-_atendimento.png). Amostras desktop/celular inspecionadas visualmente.
- O navegador detectou ambiguidade da expressão isolada `OS` com o artigo “os”. A expressão foi removida dos modelos de conhecimento e foi acrescentado teste de regressão para intenção de planos versus ordens de serviço.
- Integração WAHA existente validada por testes com fakes HTTP; nenhuma chamada real foi necessária.
- O componente alterado no VetorOS passou por verificação de sintaxe TSX. [Resultado](docs/vetor-chat-11/vetoros-widget-syntax.json). Não se declara build completo ou homologação do site VetorOS nesta execução.

O ambiente nativo fica em `backups/chat11/`, ignorado pelo Git. PHP 8.4.26 e SQLite 3.46.1; testes em memória e navegador com arquivo SQLite exclusivo, mailer de teste e serviço HTTP apenas em `127.0.0.1:8013`. Sem container novo, migration no banco operacional ou acesso a dados reais. Os 16 arquivos entregues coincidem com a cópia verificada.

A [fotografia do banco sintético](docs/vetor-chat-11/synthetic-final-state.json) contém passagens anteriores do roteiro e a jornada final, com deduplicação para um único lead, sem propostas, prospects ou jobs. Não representa dados operacionais.

## Limitações e pendências

1. **Canal automático disponível: WebChat.** WhatsApp/WAHA mantém atendimento e integração existentes; este motor não responde automaticamente por WAHA. A associação de regras a múltiplos canais e a automação no WhatsApp permanecem pendentes.
2. Ausência é tratada por horário configurado e encaminhamento para a fila. Não há detecção de presença online dos operadores ou garantia de prazo de resposta.
3. O simulador administrativo testa intenção/resposta/ação; a conversa sequencial completa foi exercitada nos testes e no navegador, não em um simulador administrativo com sessões.
4. Homologação de banco feita em SQLite. Locks foram implementados, mas não houve ensaio de concorrência em MySQL/MariaDB, upgrade do schema operacional ou teste de carga. Esses ensaios permanecem necessários antes da publicação.
5. Regras de texto são determinísticas e dependem da configuração da equipe. Campos e modelos não solicitam senhas, certificados digitais ou dados bancários; administradores devem manter esse padrão nas respostas editadas.
6. Para publicar futuramente: revisar conteúdo e regras por tenant, ensaiar migration e rollback em banco isolado compatível, gerar imagem/build de ambos os produtos, fazer backup, aplicar migration antes de liberar o código e realizar homologação autorizada. Esta etapa não autoriza publicação ou mensagens reais.

**Entrega local funcional para WebChat, com evidências preservadas. O atendimento automático multicanal completo ainda possui as pendências acima; não foi declarado publicado ou integralmente homologado em produção.**
