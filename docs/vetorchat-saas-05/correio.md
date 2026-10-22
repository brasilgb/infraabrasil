# VETORCHAT-SAAS-05 — Consolidação, homologação e preparação operacional

Audite e conclua a implementação VETORCHAT-SAAS-04, preservando a arquitetura multitenant existente.

## Objetivo

Deixar o VetorChat preparado para atendimento real nos produtos ABrasil Sistemas, VetorOS, VetorLeads e VetorPet, com gerenciamento central no VetorLeads e integração WAHA.

## Tarefas

1. Auditar o estado do Git e resolver com segurança o rebase/conflito preexistente no VetorPet. Preservar integralmente as alterações existentes e não descartar trabalho de outras etapas.
2. Revisar as migrations, o isolamento entre tenants, produtos, conversas, operadores, regras e sessões WhatsApp.
3. Conferir a chave pública compartilhada do tenant ABrasil e a identificação específica dos quatro produtos.
4. Garantir que tenants externos tenham suas próprias chaves, configurações, permissões e sessões WAHA.
5. Revisar a interface de gerenciamento do VetorChat no VetorLeads, organizando produtos, conversas, canais, operadores, regras e configurações.
6. Validar o recebimento de mensagens pelo widget e seu armazenamento na inbox correta.
7. Validar respostas do operador pelo canal de origem e a ponte autorizada para WhatsApp.
8. Verificar webhooks WAHA, autenticação HMAC, deduplicação, ACKs, idempotência e tratamento de falhas.
9. Verificar consentimento, proteção de dados pessoais e prevenção de envio indevido para outros produtos ou tenants.
10. Conferir testes dos quatro produtos, incluindo o VetorPet.
11. Preparar configuração e procedimento de ativação na VPS, sem executá-los sem autorização.
12. Identificar exatamente o que falta para respostas inteligentes por IA e propor a próxima etapa, reutilizando o que já existe no VetorLeads.

## Diretrizes de UX/UI

Criar uma experiência de atendimento simples e profissional. Os operadores devem conseguir identificar claramente o produto, o canal, o contato e o status da conversa, sem precisar alternar entre vários painéis.

Manter configurações técnicas separadas da operação cotidiana.

## Restrições obrigatórias

- Não criar, iniciar ou modificar containers Docker locais.
- Não executar deploy, commit, push ou migrations em produção sem autorização.
- Não instalar dependências desnecessárias.
- Utilizar testes com navegador quando houver ambiente compatível.
- Se uma ferramenta de navegador falhar, utilizar outra disponível; não insistir repetidamente na mesma falha.
- Interromper testes que apresentem falhas ambientais repetitivas, documentar e avançar com verificações alternativas.
- Não enviar mensagens reais a clientes sem autorização.
- Não declarar homologação operacional sem testes reais e evidências.
- Não criar um serviço paralelo de chat nem duplicar a integração WAHA existente.

## Entrega

Atualizar `executed.md` com as alterações, testes, resultados, pendências e checklist de ativação.

Classificar explicitamente a entrega como PRONTO PARA ATIVAÇÃO, PRONTO COM RESSALVAS ou BLOQUEADO.

Separar a homologação técnica do atendimento automático com IA, identificando o que está efetivamente operacional.
