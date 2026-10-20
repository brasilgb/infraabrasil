# VETOR-CHAT-12.6 — Piloto Operacional do Atendimento VetorOS

## Objetivo

Colocar o atendimento automatizado do VetorOS em condições de uso real, aproveitando o motor multicanal já homologado.

Priorizar a experiência do visitante, a transferência humana e o registro de oportunidades comerciais.

Não desenvolver novos motores, módulos comerciais ou integrações de IA.

## 1. Auditoria e versionamento

- Ler os relatórios CHAT-12.1 a CHAT-12.5.
- Confirmar a versão atualmente publicada dos serviços.
- Comparar checkout, Git e imagens de produção.
- Identificar alterações pendentes em VetorLeads e VetorOS.
- Garantir que `.env`, segredos, backups, bancos e arquivos temporários não sejam versionados.
- Preparar os commits correspondentes à versão efetivamente homologada.
- Não executar push ou publicação adicional sem autorização.

## 2. Revisão das respostas

Revisar as 14 regras cadastradas para o tenant Anderson Brasil.

Validar:

- Apresentação comercial do VetorOS.
- Funcionalidades efetivamente disponíveis.
- Atendimento de assistências técnicas.
- Solicitação de demonstração.
- Dúvidas sobre contratação.
- Solicitação de suporte.
- Transferência para atendimento humano.
- Encaminhamento fora do expediente.

Evitar respostas genéricas e promessas comerciais não confirmadas.

Verificar conflitos entre expressões e regras.

## 3. Preparação do WebChat real

Utilizar o widget já publicado em `https://vetoros.com.br/`.

Preparar uma identidade autorizada de teste e um mecanismo de isolamento que permita exercitar as regras sem responder automaticamente aos demais visitantes.

Se o WebChat não permitir esse isolamento, implementar a menor alteração necessária e homologá-la antes de publicar.

Validar a sequência completa:

1. Visitante inicia a conversa.
2. Assistente responde.
3. Visitante manifesta interesse no VetorOS.
4. Sistema identifica e qualifica o interessado.
5. CRM registra o contato recebido.
6. Oportunidade é criada quando aplicável.
7. Conversa é transferida ao operador.
8. Operador responde.
9. Visitante recebe a resposta.
10. Atendimento é encerrado.

Utilizar identidade de teste identificável e impedir que seus registros sejam confundidos com resultados comerciais reais.

## 4. Preparação do WhatsApp

Verificar:

- Sessão WAHA provisionada.
- Associação correta ao tenant.
- Autenticidade do webhook.
- Recebimento de mensagens.
- Outbox.
- Deduplicação.
- Suspensão por operador.
- Opt-out e cancelamento.
- Recuperação de falhas.

Manter a trava global de envio desligada.

Não enviar mensagem real nem ativar regras WhatsApp nesta execução sem autorização específica de sessão, número de destino, conteúdo e escopo do teste.

## 5. Atendimento humano

Garantir que o operador consiga:

- Visualizar novas conversas.
- Identificar o canal.
- Assumir o atendimento.
- Responder pelo painel.
- Consultar histórico.
- Associar contato ao CRM.
- Encerrar a conversa.
- Devolver ao atendimento automático, quando apropriado.

Confirmar que o robô não responda simultaneamente ao operador.

## 6. Indicadores comerciais

Conferir se os indicadores permitem distinguir:

- Visitantes atendidos.
- Contatos identificados.
- Conversas automatizadas.
- Transferências humanas.
- Oportunidades comerciais.
- Atendimentos encerrados.

Não incluir interações sintéticas nos indicadores comerciais reais.

## 7. Critérios para ativação pública

Somente recomendar a ativação geral depois de confirmar:

- Conteúdo revisado.
- Horário/fuso configurados e aprovados.
- Operador disponível ou fila funcional.
- Jornada WebChat aprovada.
- CRM funcionando.
- Desativação imediata testada.
- Travas WhatsApp verificadas.
- Ausência de regressões.

Não ativar automaticamente as 14 regras produtivas.

## 8. Entrega

Atualizar `executed.md` com:

- Situação do versionamento.
- Resultado da revisão das regras.
- Testes do WebChat.
- Testes WhatsApp sem envio externo.
- Integração com CRM.
- Atendimento humano.
- Indicadores.
- Pendências.
- Evidências.
- Recomendação de liberação operacional.

Distinguir claramente homologação sintética, homologação controlada no ambiente operacional e atendimento público efetivamente ativado.

**Critério de conclusão:** WebChat validado ponta a ponta com identidade isolada, WhatsApp preparado para teste real autorizado e operação protegida contra ativação pública involuntária.
