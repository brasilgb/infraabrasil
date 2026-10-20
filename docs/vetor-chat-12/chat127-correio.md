# VETOR-CHAT-12.7 — Sandbox de Atendimento e Preparação do Piloto

## Objetivo

Implementar um mecanismo seguro e reutilizável para homologar as automações do VetorLeads, permitindo testar conversas completas do WebChat antes da ativação pública.

O sandbox deverá utilizar o motor real de atendimento, mas não poderá interferir nos clientes, campanhas, CRM, indicadores ou conversas operacionais.

Evitar novas auditorias gerais e reimplementações de módulos existentes.

## 1. Preparação

- Ler o relatório CHAT-12.6 e os arquivos relevantes da CHAT-12.5.
- Inspecionar o motor SupportAutomation, WebChatController, Central, CRM e regras existentes.
- Identificar os pontos mínimos de alteração.
- Preservar todas as funcionalidades atuais.
- Utilizar a infraestrutura existente, sem novos serviços pagos.

## 2. Sandbox isolado por tenant

Criar uma área administrativa denominada **Laboratório de Atendimento**, dentro de Automação de Atendimento.

O administrador deverá conseguir:

- Escolher o canal a simular.
- Selecionar regras ativas ou rascunhos.
- Iniciar uma conversa de teste.
- Enviar mensagens sucessivas.
- Visualizar respostas automáticas.
- Acompanhar a qualificação.
- Simular transferência humana.
- Simular retorno ao robô.
- Reiniciar o teste.
- Consultar decisões e regras utilizadas.

A simulação deve preservar o contexto entre mensagens.

Não limitar o laboratório à prévia isolada de uma única intenção.

## 3. Isolamento obrigatório

Preferir armazenamento segregado ou banco de homologação isolado.

Se utilizar as mesmas estruturas de conversa:

- Identificar explicitamente todas as entidades sintéticas.
- Garantir escopo de sandbox em consultas, relacionamentos, eventos e jobs.
- Excluir registros sintéticos de indicadores comerciais.
- Impedir criação de leads, propostas ou oportunidades reais.
- Impedir disparo de campanhas.
- Impedir envio externo pelo WebChat ou WAHA.
- Não utilizar sessões WhatsApp produtivas.
- Não permitir que regras em teste sejam executadas em conversas públicas.

Um simples campo `is_test` não é suficiente sem garantias de isolamento em toda a aplicação.

## 4. Teste controlado do widget

Preparar mecanismo para homologar também o widget real do VetorOS.

O acesso deverá ser restrito a uma identidade de teste autenticada e autorizada pelo servidor.

Não utilizar parâmetros públicos simples, como `?test=true`, para habilitar o sandbox.

Preferir sessão de homologação temporária vinculada ao tenant e emitida pelo servidor, com expiração e revogação.

O visitante comum deve continuar usando exclusivamente o fluxo operacional.

Rascunhos não podem ser expostos ou executados por usuários públicos.

## 5. Qualificação e CRM

No laboratório, testar:

1. Identificação do visitante.
2. Coleta de nome e empresa.
3. Reconhecimento do telefone.
4. Identificação do interesse.
5. Classificação comercial.
6. Criação simulada de contato.
7. Criação simulada de oportunidade.
8. Transferência para operador.
9. Resposta humana simulada.
10. Encerramento.

Exibir claramente os registros que seriam criados em produção, sem efetivamente criá-los.

## 6. Revisão das respostas

Revisar as 14 regras do VetorOS.

Na regra de planos, retirar a afirmação de que o pagamento é exclusivamente Pix enquanto a condição não estiver confirmada.

Identificar a divergência com a FAQ pública e preparar a correção correspondente, sem anunciar uma modalidade de pagamento ainda não homologada.

Manter a regra fora do expediente inativa enquanto não existir agenda aprovada.

Não presumir disponibilidade de atendentes ou agendamento automático.

## 7. Atendimento humano

No laboratório, permitir simular:

- Assumir conversa.
- Atribuir responsável fictício ou operador autorizado de teste.
- Enviar resposta humana interna.
- Suspender automação.
- Retomar automação.
- Encerrar atendimento.

Essas operações não poderão alterar filas operacionais.

## 8. Interface

Criar uma experiência organizada, integrada à administração existente.

Sugestão:

Automação de Atendimento
- Visão geral.
- Regras e respostas.
- Laboratório.
- Histórico de decisões.
- Configurações.

O Laboratório deve mostrar a conversa à esquerda ou no centro e, ao lado, informações de intenção, regra aplicada, estado da qualificação e eventos.

Adaptar a disposição para celular.

## 9. Testes obrigatórios

Executar:

- Regressão PHP completa.
- Testes de isolamento multitenant.
- Testes de autorização do laboratório.
- Testes de ausência de efeitos comerciais.
- Testes de ausência de envios externos.
- Testes de sessão expirada e revogada.
- Testes de compartilhamento de regras.
- Testes de transferência humana.
- PHPStan.
- TypeScript.
- ESLint.
- Build Vite.
- Chromium desktop e celular.

Executar os testes com banco isolado e proteção CHAT-12.5.

## 10. Publicação e segurança

Implementar e homologar localmente primeiro.

Não publicar automaticamente.

Não ativar regras operacionais.

Não habilitar envio automático WhatsApp.

Não enviar mensagens reais.

Não alterar registros comerciais existentes.

## 11. Entrega

Atualizar executed.md contendo:

- Arquitetura do laboratório.
- Arquivos modificados.
- Isolamento implementado.
- Jornadas simuladas.
- Testes executados.
- Evidências visuais.
- Revisão das 14 respostas.
- Pendências para piloto operacional.
- Estado das travas.
- Instruções de publicação.
- Diferenças Git/imagens relevantes.

**Critério de conclusão:** o administrador consegue executar uma conversa completa com as regras reais do tenant, incluindo qualificação e transferência, sem interferir nos dados ou atendimentos públicos.
