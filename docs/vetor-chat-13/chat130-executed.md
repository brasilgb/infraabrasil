# Execução — VETOR-CHAT-13.0

Preparação técnica entregue e publicada. **Piloto comercial supervisionado ainda bloqueado** por operador sem e-mail verificado, plantão/responsável não confirmado e política de atendimento não definida. Não houve ativação pública, ativação de regras ou envio de WhatsApp.

## Comparação do correio

O `correio.md` mudou de CHAT-12.9 para CHAT-13.0. A solicitação atual está arquivada em [chat130-correio.md](docs/vetor-chat-13/chat130-correio.md), e o relatório anterior em [previous-executed.md](docs/vetor-chat-13/previous-executed.md).

- SHA-256 atual: `483144ee31250b5a46773b4b1520ca49dc9d96319f3622ad0096a8e1a910e922`.
- SHA-256 anterior CHAT-12.9: `e127c709c8b10562a24c3c1d206bb40c6f616746389941f9313f35638277e20f`.
- Execução em 19/10/2026 UTC. Não foi realizada nova auditoria geral nem implementada IA.

## Versão e publicação

Confirmado o estado publicado CHAT-12.9 antes da mudança:

- VetorLeads: commit `29a8504af355878eedd47337df72d06e0ebbd407`, imagem `vetorleads:chat129-29a8504af355`, ID `sha256:3c7ab706546aac5ccbd12812b19a46be8262260ee06b59e8bb8d32b2ac9be22a`.
- VetorOS: commit `1700256f53fda990ed2f812af966c86cc50399ee`, imagem `vetoros:chat129-1700256f53fd`, ID `sha256:eb6b85c0d694b4e30a6436ab0906ffc5fd4a9e868d60c0267872a289ed25dbfc`.

A preparação CHAT-13.0 foi publicada somente no VetorLeads/API, worker e scheduler:

- Commit: `859241bd9bbc48bdd3353587f914d13f97237127`.
- Imagem: `vetorleads:chat130-859241bd9bbc`.
- ID local imutável: `sha256:5ab2572d41cfc2f0ce4fc8377b62317109415256080da7e285c53f5c447aca93`. Imagem construída localmente, sem push de registry.
- Commit de infraestrutura: `5a25407ee1fba38cadc4f965a3587e924547dd82`.
- VetorOS/API, worker e scheduler permaneceram na imagem CHAT-12.9 acima.
- Os seis serviços estão saudáveis; 21 containers saudáveis no encerramento. Nenhuma migration pendente.
- Variáveis de ambiente, credenciais, mounts e healthchecks preservados; nenhuma variável operacional alterada.

Evidências: [imagens publicadas](docs/vetor-chat-13/published-images.json), [preflight](docs/vetor-chat-13/release-preflight.json), [saúde final](docs/vetor-chat-13/containers-after.json).

## Regras revisadas e conflitos

Tenant operacional: Anderson Brasil, ID 1. Antes da alteração, confirmados os valores 10 e 80, a existência das 14 regras e todas inativas. Aplicadas exclusivamente as prioridades aprovadas:

- Regra 2, Apresentação do VetorOS: **10 → 200**.
- Regra 9, Notas fiscais: **80 → 65**, antes de Vendas, caixa e financeiro (70).

Textos, palavras-chave, ações, assuntos, canais e horários preservados. Auditoria com snapshots antes/depois registrada nos IDs 17 e 18, com origem expressa CHAT-13.0. Nenhuma regra ativada. Defaults genéricos de novos tenants não foram alterados: o ajuste solicitado incidiu sobre as 14 regras do tenant Anderson Brasil.

| ID | Regra | Prioridade final | Intenção / comportamento | Estado |
| --- | --- | --- | --- | --- |
| 1 | Boas-vindas e identificação | 5 | Identificação inicial; sem oportunidade | Inativa |
| 2 | Apresentação do VetorOS | 200 | Informação | Inativa |
| 3 | Assistências técnicas | 20 | Informação | Inativa |
| 4 | Clientes e equipamentos | 30 | Informação | Inativa |
| 5 | Ordens de serviço | 40 | Informação | Inativa |
| 6 | Orçamentos e aprovação | 50 | Informação | Inativa |
| 7 | Peças e estoque | 60 | Informação | Inativa |
| 8 | Vendas, caixa e financeiro | 70 | Informação | Inativa |
| 9 | Notas fiscais | 65 | Informação fiscal | Inativa |
| 10 | Planos e contratação | 90 | Comercial / qualificação | Inativa |
| 11 | Agendamento de demonstração | 100 | Comercial / qualificação | Inativa |
| 12 | Suporte técnico | 110 | Suporte / humano | Inativa |
| 13 | Atendimento fora do expediente | 1000 | Fora do expediente; configuração pendente | Inativa |
| 14 | Transferência para atendente humano | 1 | Atendimento humano | Inativa |

No Laboratório publicado, com cópias sintéticas dessas regras e prioridades finais:

- “sistema para controle de estoque e peças” → **Peças e estoque**, sem interceptação da apresentação geral.
- “financeiro com emissão de nota fiscal” → **Notas fiscais**, antes da regra financeira genérica.
- Interesse em demonstração → qualificação comercial; suporte/humano → transferência sem prévia de oportunidade.

Evidências: [prioridades aplicadas](docs/vetor-chat-13/priorities-applied.json), [observações do Laboratório](docs/vetor-chat-13/published-lab.json), [auditoria final](docs/vetor-chat-13/runtime-after.json).

## Identificação progressiva e CRM

Preservado o motor determinístico existente. A qualificação reaproveita nome, empresa, telefone e e-mail já informados; somente pede campos faltantes, chegando ao interesse. E-mail e empresa não são apagados quando omitidos em uma identificação posterior. A busca por telefone/e-mail associa o contato existente e evita duplicação.

A finalização comercial associa oportunidade, atribui responsável confirmado e mantém acompanhamento de vendedor. Suporte não cria oportunidade automaticamente. Não há inscrição implícita em campanhas: consentimento continua independente. Handoff e qualificação concluída encaminham para a fila do responsável configurado, sem promessa de resposta imediata.

Comprovado em testes HTTP isolados: dois chats com o mesmo contato produzem um único lead, reaproveitam os dados, preservam e-mail/empresa e registram vendedor/follow-up sem consentimento de campanha. No publicado, somente prévias simuladas; nenhum lead ou oportunidade real foi criado para homologação.

## Operador, Central e notificações

Estado real verificado:

- Anderson Brasil, usuário 1: ativo, vínculo `owner`, autorizado a administrar e acessar a Central.
- **`email_verified_at` está nulo.** Não foi alterado, não foi enviada verificação por e-mail e não foi presumida verificação ou disponibilidade de plantão.
- A fila existente contém 2 WebChats em andamento e 1 conversa WhatsApp pendente; não foram respondidos, assumidos ou encerrados durante esta execução.
- A Central possui atualização periódica da fila e indicadores de não lidas; não foi acrescentado envio de e-mail/push.
- Assumir, responder, suspender, retomar e encerrar foram validados com dados sintéticos no Laboratório; pausa após resposta humana e deduplicação também estão cobertas por regressão isolada.

Isso comprova o funcionamento técnico, **não comprova um operador real em plantão**. O responsável precisa verificar o próprio e-mail e confirmar a operação antes da liberação.

## Horário e checklist na administração

Disponível em `/settings/support-automation`, seção **Liberação comercial do WebChat**:

- Operador autorizado selecionável, com aviso para e-mail não verificado.
- Política “Ainda não definida”, atendimento assíncrono com mensagem explícita ou expediente com fuso IANA, dias, início, término e resposta externa obrigatórios.
- Validação de fuso/dias/faixa de horário; nenhum horário foi presumido.
- Checklist de revisão, conflitos, operador, fila, widget, CRM, desligamento, backup e regressão.
- Ativação futura exige administrador com e-mail verificado, operador verificado, checklist completo e confirmação literal `ATIVAR WEBCHAT`.
- Salvar preparação mantém/desliga a automação. Botão para desligamento imediato. Alterações de preparação/ativação são auditadas.
- Editar uma regra para ativa não contorna a trava pública. Regra fora do expediente não pode ser ativada sem horário.

**Estado operacional salvo:** configuração de piloto ainda nula, interpretada como desligada; responsável não selecionado, política `unset`, horários ausentes. A regra “Atendimento fora do expediente” permanece inativa. Os campos de conferência administrativa permanecem pendentes até o administrador atestar a liberação; os testes técnicos abaixo não substituem essa confirmação.

| Condição | Resultado desta execução |
| --- | --- |
| 14 regras revisadas | Concluído; todas inativas |
| Conflitos aprovados | Corrigidos e conferidos no Laboratório publicado |
| Operador autorizado e verificado | **Pendente: e-mail não verificado; responsável/plantão não confirmado** |
| Fila e atendimento humano | Fluxo técnico testado; operação real pelo responsável pendente |
| Horário ou política assíncrona explícita | **Pendente: não informado** |
| Widget e sandbox | Homologação publicada concluída com código temporário |
| CRM/deduplicação | Regressão isolada aprovada; publicado validado apenas em prévia |
| Desligamento imediato | Testado em base isolada; publicado permanece desligado |
| Backup válido | Restaurado e migration ensaiada; integridade preservada |
| Ausência de regressão | Suíte completa e testes afetados aprovados |
| Confirmação administrativa para ativação | **Não solicitada/não executada nesta fase** |

## Indicadores reais

Dashboard passou a exibir o acumulado operacional de WebChat do tenant, com conversão indisponível quando não há denominador. Estado real no encerramento:

| Indicador | Valor |
| --- | --- |
| Conversas iniciadas | 2 |
| Conversas respondidas pelo assistente | 0 |
| Contatos qualificados | 0 |
| Oportunidades comerciais associadas | 0 |
| Transferências para humanos | 0 |
| Atendimentos pendentes | 0 |
| Conversões comerciais | 0 |
| Conversão (%) | Indisponível: sem oportunidades associadas |

“Atendimentos pendentes” nessa seção conta WebChats pendentes com automação suspensa, não os dois já em andamento nem a fila WhatsApp. Oportunidades são leads comerciais distintos associados a chats comerciais; conversão usa esses mesmos leads ganhos.

Laboratório usa tabelas separadas. Novos marcadores persistentes `is_synthetic` em conversas e leads permitem excluir registros sintéticos também fora do Laboratório, sem parâmetro público para marcá-los/desmarcá-los. As consultas afetadas do dashboard, atividades e métricas comerciais excluem esses registros. Testes com dados sintéticos e sandbox comprovaram a exclusão; o dashboard do tenant temporário permaneceu zerado mesmo durante a homologação.

## Testes e isolamento

- Suíte VetorLeads: **1.325 testes / 14.046 assertions aprovados**.
- Regressão final dos fluxos afetados: **40 testes / 407 assertions aprovados**.
- Novos cenários de piloto: 7 testes / 83 assertions, incluídos nas contagens acima.
- PHPStan: zero erros. TypeScript, ESLint e Pint aprovados. Build da imagem/frontend aprovado.
- Testes automatizados executados em base isolada; nenhum Pest/PHPUnit foi executado contra o banco operacional.
- Regras inativas silenciosas publicamente, trava global, ativação explícita, permissões, e-mail não verificado, agenda inválida, deduplicação, identificação progressiva, handoff/pausa/retomada e exclusão de sintéticos cobertos pela regressão.
- Laboratório publicado: conflitos, qualificação somente em prévia, handoff, assumir, resposta humana, silêncio durante pausa, retomada e encerramento aprovados.
- Widget publicado: código temporário autorizado, troca de mensagens no sandbox, revogação e bloqueio após reload aprovados. **Zero chamadas às rotas operacionais de conversa e zero erros JavaScript**.
- Interface inspecionada em 1440 px e 390 px. Capturas em `docs/vetor-chat-13/`.
- Tenant sintético 31 e usuário sintético 25 removidos após exportar histórico e revogar acessos. Contagens operacionais antes/depois idênticas: leads 1, atividades 4, conversas 3, mensagens 8, eventos 5; outbox 0, jobs 0, failed_jobs 0.

A revisão automática bloqueou uma tentativa de teste negativo que enviaria `enabled=true` ao endpoint publicado, mesmo esperando rejeição. A chamada não foi executada: o cenário ficou restrito aos testes isolados, evitando risco de ativação acidental. Não é necessário aprovar esse teste para concluir a preparação.

Evidências: [resumo de validação](docs/vetor-chat-13/validation-summary.json), [isolamento final](docs/vetor-chat-13/final-isolation.json), [histórico sintético exportado](docs/vetor-chat-13/synthetic-sandbox-history.json), [dashboard sintético](docs/vetor-chat-13/1440-dashboard.png).

## Backup, migration e retorno

- Backup privado: `/opt/infra-abrasil/backups/chat130/vetorleads-before-chat130.sql`, permissões 0600, 511284 bytes.
- SHA-256: `12328edef31175e002d8efa3c1949b850c71d7b9a20d83e839e264caf6d351e0`.
- Restaurado no banco isolado `chat130_rehearsal`, com credencial restrita a esse banco.
- Migration `2026_10_19_220000_prepare_web_chat_commercial_pilot` ensaiada antes da aplicação operacional. Assinaturas dos dados originais de **49 tabelas** preservadas na restauração; somente colunas aditivas e registro de migration novos.
- Credenciais de banco, códigos e senhas temporários não incluídos no relatório ou no Git; mecanismos e credenciais existentes de sandbox preservados.
- Retorno de código disponível pela imagem anterior CHAT-12.9 nos três serviços Leads. Manter colunas aditivas e auditoria; não executar rollback destrutivo de migration. A imagem anterior exige preservar todas as regras inativas. As prioridades aprovadas podem permanecer.

## Git e pendências

Código VetorLeads registrado no commit `859241bd9bbc48bdd3353587f914d13f97237127`; deploy de preparação no commit `5a25407ee1fba38cadc4f965a3587e924547dd82`. VetorOS sem alteração. Alteração anterior do VetorPet e documentos de outras tarefas preservados e excluídos dos commits CHAT-13.0.

Para atingir o critério de piloto supervisionado: **verificar o e-mail do responsável, confirmar quem operará a fila, definir expediente completo ou mensagem/política assíncrona explícita e obter a conferência administrativa**. Nenhuma dessas decisões foi inventada. A ativação pública exige autorização específica em uma próxima fase.

**Travas finais:** 14/14 regras inativas, piloto WebChat desligado, regra fora do expediente inativa, WhatsApp global `false`, WhatsApp do tenant desligado, homologação WhatsApp ausente, outbox vazia. Não houve envio automático.
