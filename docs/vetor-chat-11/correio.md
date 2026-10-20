# VETOR-CHAT-11 — Atendimento automatizado do VetorOS

## Objetivo

Implementar um módulo de respostas automáticas para o atendimento institucional do VetorOS, aproveitando a infraestrutura existente do VetorLeads, VetorChat e WAHA.

A implementação deve evoluir o atendimento já desenvolvido, sem criar outro sistema de mensagens ou duplicar funcionalidades.

## 1. Auditoria inicial obrigatória

- Identificar a implementação atual do WebChat e sua integração com o VetorLeads.
- Verificar o funcionamento de mensagens recebidas, enviadas, histórico e transferência para operadores.
- Identificar como os atendimentos são associados aos tenants e aos canais.
- Verificar a configuração do WhatsApp via WAHA.
- Reutilizar os serviços, modelos, rotas e componentes existentes.
- Preservar todas as funcionalidades atuais.

## 2. Motor de automação

Criar um mecanismo de respostas automáticas baseado em regras configuráveis.

Recursos:
- Mensagem de boas-vindas.
- Identificação de intenção.
- Palavras-chave e expressões equivalentes.
- Respostas condicionais.
- Perguntas sequenciais para qualificação.
- Solicitação de nome, empresa, telefone e interesse.
- Transferência para atendimento humano.
- Tratamento de mensagens não compreendidas.
- Horários comerciais e ausência de operadores.
- Prevenção de respostas repetidas e loops.
- Histórico completo de decisões e mensagens automáticas.

Não utilizar IA paga nesta primeira implementação.

## 3. Base de conhecimento do VetorOS

Criar uma base editável para dúvidas frequentes:

- O que é o VetorOS.
- Segmentos de assistência técnica atendidos.
- Cadastro de clientes e equipamentos.
- Ordens de serviço.
- Orçamentos e peças.
- Estoque, compras e vendas.
- Caixa e financeiro.
- Emissão de notas fiscais.
- Relatórios e indicadores.
- Planos, contratação e demonstrações.
- Orientações gerais de suporte.

As respostas devem utilizar funcionalidades efetivamente disponíveis, sem prometer recursos não implementados.

## 4. Administração

No VetorLeads, criar uma área de Automação de Atendimento com:

- Listagem de regras.
- Cadastro, edição e exclusão lógica.
- Ativação e desativação.
- Ordenação por prioridade.
- Associação por tenant e canal.
- Editor de mensagens.
- Simulador de conversa.
- Indicadores de execução.
- Histórico de alterações.

Manter configurações no final do menu principal, seguindo o padrão visual atual.

## 5. Integração comercial

Quando o visitante demonstrar interesse comercial:

- Identificar o contato existente antes de criar novo registro.
- Registrar a origem como atendimento do site VetorOS.
- Qualificar o interesse.
- Encaminhar ao CRM do tenant correto, evitando duplicidades.
- Registrar atividades e histórico de conversa.
- Criar tarefa de acompanhamento quando apropriado.
- Nunca fechar uma venda ou emitir proposta automaticamente sem autorização explícita.

Não misturar prospects captados por prospecção ativa com contatos recebidos nos sites.

## 6. Atendimento humano

- Permitir assumir a conversa.
- Suspender a automação quando o operador assumir.
- Permitir devolução ao atendimento automático.
- Manter histórico da conversa.
- Exibir claramente se a mensagem foi enviada pelo robô ou por um operador.
- Não enviar mensagens automáticas repetidas após transferência.

## 7. Segurança e LGPD

- Isolamento absoluto entre tenants.
- Impedir acesso cruzado a conversas e bases de conhecimento.
- Proteção contra duplicação e reprocessamento de eventos.
- Controle de permissões administrativas.
- Não solicitar senhas, certificados digitais ou dados bancários no chatbot.
- Respeitar consentimentos e regras aplicáveis de comunicação, inclusive no WhatsApp.

## 8. Homologação

Executar testes automatizados e simulações completas com dados sintéticos.

Validar:
- Atendimento pelo WebChat.
- Identificação de intenção.
- Respostas corretas.
- Captura de contato.
- Integração com CRM.
- Transferência para humano.
- Retorno ao fluxo automático.
- Isolamento entre tenants.
- Responsividade desktop/mobile.

Documentar funcionalidades, testes, limitações e pendências.

## Restrições

Priorizar baixo custo, reaproveitamento da infraestrutura e simplicidade operacional.

Não introduzir serviços pagos, novo container ou nova arquitetura sem justificativa técnica.

Não realizar deploy, push ou envio de mensagens reais nesta etapa sem autorização.

Registrar o resultado em `executed.md`, preservando as evidências técnicas.
