# VETOR-CHAT-13.0 — Preparação da Ativação Comercial do WebChat VetorOS

## Objetivo

Preparar o WebChat do VetorOS para atender visitantes reais, qualificar interessados e encaminhar oportunidades comerciais ao VetorLeads.

Aproveitar integralmente o motor de automação, o Laboratório e a Central de Atendimento já existentes.

Não implementar IA nesta etapa, não reconstruir módulos e não realizar nova auditoria geral.

## 1. Verificação da versão publicada

- Confirmar os commits e imagens CHAT-12.9.
- Verificar a saúde do VetorLeads, VetorOS, worker e scheduler.
- Confirmar integridade das migrations e do Laboratório.
- Preservar os mecanismos de sandbox e suas credenciais.
- Garantir que o WhatsApp automático continue desligado.

## 2. Ajustar as regras comerciais

Revisar as 14 regras do tenant Anderson Brasil.

Aplicar os ajustes de prioridade já aprovados tecnicamente no Laboratório:

- Apresentação geral: prioridade 10 para 200.
- Notas fiscais: prioridade 80 para 65.

Antes de alterar, confirmar que os valores atuais ainda correspondem ao relatório e verificar conflitos com outras regras.

Preservar conteúdos e registrar auditoria das alterações.

Não ativar automaticamente todas as regras.

## 3. Organizar atendimento comercial

Separar claramente três intenções:

**Informações sobre o VetorOS**
- Apresentação do produto.
- Funcionalidades.
- Segmentos atendidos.
- Dúvidas frequentes.

**Interesse comercial**
- Contratação.
- Planos.
- Demonstração.
- Solicitação de contato.
- Qualificação e criação de oportunidade.

**Suporte**
- Cliente existente.
- Dúvidas operacionais.
- Problemas de acesso.
- Solicitação de atendimento humano.

Suporte não deverá criar oportunidade comercial automaticamente.

## 4. Identificação progressiva

O assistente deverá solicitar somente as informações necessárias, aproveitando dados fornecidos anteriormente.

Fluxo comercial:

1. Identificar a intenção.
2. Responder à dúvida inicial.
3. Solicitar nome quando necessário.
4. Solicitar empresa.
5. Solicitar telefone ou WhatsApp com DDD.
6. Registrar interesse comercial.
7. Criar ou associar contato.
8. Criar oportunidade quando aplicável.
9. Encaminhar para acompanhamento do vendedor.

Não presumir consentimento para campanhas.

## 5. Atendimento humano

Verificar operacionalmente:

- Existência de operador autorizado e com e-mail verificado.
- Permissões de acesso à Central.
- Fila de conversas aguardando atendimento.
- Notificação de nova conversa, quando disponível.
- Assumir atendimento.
- Responder ao visitante.
- Suspender automação.
- Encerrar atendimento.

Não declarar disponibilidade de atendimento imediato sem operador ou plantão confirmado.

## 6. Horário comercial

Disponibilizar uma configuração simples e obrigatória para:

- Fuso horário.
- Dias da semana.
- Início e término do expediente.
- Resposta fora do expediente.
- Encaminhamento ao responsável.

Não presumir horários.

Enquanto não forem definidos, manter a regra específica de fora do expediente inativa e não prometer atendimento humano imediato.

## 7. Preparação da ativação pública

Criar checklist de liberação na administração.

Condições obrigatórias:

- Regras revisadas.
- Conflitos corrigidos.
- Operador confirmado.
- Fila validada.
- Horário definido ou política explícita de atendimento assíncrono.
- WebChat funcionando.
- CRM recebendo oportunidades corretamente.
- Mecanismo de desligamento imediato testado.
- Backup válido.
- Ausência de regressão.

A ativação pública deve exigir confirmação explícita do administrador.

**Não executar a ativação pública nesta fase.**

## 8. Indicadores comerciais

Verificar que o dashboard apresenta dados reais de:

- Conversas iniciadas.
- Conversas respondidas pelo assistente.
- Contatos qualificados.
- Oportunidades comerciais.
- Transferências para humanos.
- Atendimentos pendentes.
- Conversões comerciais, quando houver dados suficientes.

Excluir permanentemente os dados sintéticos desses indicadores.

## 9. Testes

Executar testes direcionados e regressão dos fluxos afetados.

Utilizar o Laboratório publicado para verificar as respostas e os conflitos corrigidos, sem utilizar contatos comerciais reais.

Confirmar que:

- Regras inativas não respondem publicamente.
- O modo sandbox continua isolado.
- O WhatsApp permanece desligado.
- O CRM não cria oportunidades duplicadas.
- A transferência humana suspende o robô.
- A retomada funciona corretamente.

## 10. Entrega

Atualizar `executed.md` com:

- Regras revisadas e prioridades aplicadas.
- Estado do operador e da fila.
- Situação dos horários.
- Resultado dos testes.
- Indicadores disponíveis.
- Pendências para ativação pública.
- Estado das travas.
- Checklist de liberação.
- Eventuais alterações de código e Git.

**Critério de conclusão:** WebChat pronto para piloto comercial supervisionado, com regras corretas, atendimento humano operacional e integração CRM validada.

Não ativar regras públicas nem enviar mensagens WhatsApp sem autorização específica.
