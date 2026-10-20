# VETOR-CHAT-12 — Atendimento Automatizado Multicanal (WebChat + WhatsApp)

## Objetivo

Evoluir a automação existente do VetorLeads para disponibilizar atendimento automatizado via WhatsApp/WAHA e WebChat, com gerenciamento centralizado, qualificação comercial, atendimento humano e histórico unificado.

**Implementar efetivamente, não apenas apresentar uma análise.**

## 1. Auditoria inicial

Antes de modificar o sistema:

- Ler `correio.md`, `executed.md` e os relatórios VETOR-CHAT-10 e VETOR-CHAT-11.
- Identificar os serviços de automação, modelos, migrations, APIs, webhooks WAHA e interfaces existentes.
- Reutilizar integralmente o motor `SupportAutomation`.
- Preservar o funcionamento do WebChat e da Central de Atendimento.
- Verificar alterações locais antes de editar qualquer arquivo.

Não criar um segundo motor de chatbot.

## 2. Automação via WhatsApp/WAHA

Integrar o motor atual aos eventos de mensagens recebidas pelo WAHA.

Implementar:

- Identificação da sessão WhatsApp pertencente ao tenant.
- Resolução segura do tenant exclusivamente pelo vínculo servidor/sessão.
- Processamento de mensagens recebidas.
- Identificação das regras ativas aplicáveis ao canal WhatsApp.
- Respostas automáticas pelo WAHA.
- Controle de duplicidade, concorrência e reprocessamento.
- Registro da mensagem e da decisão no histórico.
- Suspensão automática quando um atendente assumir.
- Transferência para atendimento humano.
- Retorno manual ao atendimento automatizado.

**Importante:** mensagens originadas pelo próprio sistema, operadores ou pelo robô não podem disparar novas respostas automáticas.

Não permitir envio automático real até a homologação e ativação explícita do tenant.

## 3. Configurações de automação

Aproveitar a área administrativa existente de Automação de Atendimento.

Cada regra deverá permitir selecionar:

- Somente WebChat.
- Somente WhatsApp.
- Ambos os canais.

Não duplicar regras quando forem compartilhadas entre canais.

Manter prioridade, horário de atendimento, ativação, desativação, auditoria e simulador.

O administrador precisa conseguir testar as respostas sem enviar mensagens reais.

## 4. Atendimento inteligente e contextual

Implementar memória operacional limitada ao contexto da conversa e ao histórico autorizado do cliente.

O assistente deverá reconhecer informações já fornecidas, como:

- Nome.
- Empresa.
- Telefone.
- Interesse comercial.
- Assunto do atendimento.
- Etapa de qualificação.

Evitar solicitar novamente informações válidas já registradas.

Utilizar inicialmente regras determinísticas e os dados disponíveis no CRM, sem dependência obrigatória de IA paga.

Não utilizar histórico de outros tenants.

## 5. Qualificação e integração comercial

Quando a conversa demonstrar interesse comercial, permitir:

1. Identificar o contato existente.
2. Criar contato quando necessário.
3. Associar a conversa ao CRM.
4. Registrar o produto ou serviço de interesse.
5. Criar oportunidade comercial quando aplicável.
6. Encaminhar ao responsável comercial.
7. Registrar o acompanhamento necessário.

Evitar criação duplicada de leads ou oportunidades.

Diferenciar claramente os registros:

- Prospect captado por prospecção ativa.
- Contato recebido espontaneamente.
- Oportunidade comercial.
- Cliente existente.
- Solicitação de suporte.

Não transformar automaticamente toda mensagem recebida em oportunidade comercial.

## 6. Central de Atendimento

Organizar a interface para trabalhar com WebChat e WhatsApp em uma única Central.

A interface deverá permitir:

- Filtrar conversas por canal.
- Visualizar conversas aguardando atendimento.
- Identificar conversas em atendimento automático.
- Identificar conversas assumidas por operadores.
- Consultar histórico.
- Assumir atendimento.
- Transferir responsável.
- Devolver ao automático.
- Encerrar atendimento.

Exibir claramente o canal, a situação da automação e o responsável pela conversa.

Evitar controles duplicados e excesso de informações na interface.

As configurações administrativas deverão permanecer no final do menu.

## 7. Indicadores operacionais

Adicionar indicadores reais, derivados dos eventos registrados:

- Conversas recebidas por canal.
- Conversas atendidas automaticamente.
- Conversas transferidas para operadores.
- Contatos qualificados.
- Oportunidades criadas.
- Conversas aguardando atendimento.
- Falhas no envio automático.

Criar filtros por período e canal.

Não apresentar dados demonstrativos como indicadores operacionais reais.

## 8. Segurança e confiabilidade

Obrigatório:

- Respeitar o isolamento multitenant.
- Garantir que uma sessão WAHA não possa acessar informações de outro tenant.
- Preservar validação HMAC dos webhooks.
- Implementar proteção contra reenvio e loops.
- Utilizar identificadores idempotentes.
- Evitar disparos em massa.
- Respeitar cancelamento, opt-out e transferência humana.
- Não solicitar senhas, certificados digitais ou dados financeiros sensíveis.
- Registrar falhas sem expor tokens.
- Evitar que erros de envio provoquem perda ou duplicação de mensagens.

Separar o registro da decisão automática do envio externo, com mecanismo confiável de entrega e recuperação de falhas.

## 9. Homologação

Executar os testes existentes e acrescentar testes específicos para:

- Recebimento WhatsApp.
- Regras compartilhadas entre canais.
- Respostas automáticas.
- Ausência de regras.
- Fora do horário.
- Atendimento humano.
- Retorno ao robô.
- Mensagens repetidas.
- Falha temporária do WAHA.
- Reconexão da sessão.
- Isolamento entre tenants.
- Permissões administrativas.
- Concorrência real com MariaDB.
- Compatibilidade com o WebChat anterior.

Validar também as interfaces desktop e celular.

Utilizar WAHA simulado nos testes automatizados. Não enviar mensagens reais sem autorização.

## 10. Limites da execução

- Desenvolvimento local sem Docker.
- Reutilizar MariaDB local e serviços existentes.
- Não instalar novos serviços quando a infraestrutura atual atender.
- Não implementar IA paga nesta fase.
- Não disparar campanhas.
- Não alterar contatos comerciais reais.
- Não executar deploy ou migrations operacionais sem homologação.
- Não realizar push automaticamente.

## 11. Entrega

Atualizar `executed.md` com:

1. Funcionalidades efetivamente implementadas.
2. Arquivos modificados.
3. Migrations criadas.
4. Resultados dos testes.
5. Evidências da interface.
6. Evidências do isolamento multitenant.
7. Pendências encontradas.
8. Instruções seguras para publicação.
9. O que ainda depende de configuração ou teste real.

Não declarar homologação em produção quando somente testes locais tiverem sido executados.

**Critério de conclusão:** WebChat e WhatsApp devem utilizar o mesmo motor de automação, com configurações por tenant, continuidade do atendimento humano, histórico unificado e testes aprovados, sem comprometer os canais existentes.
