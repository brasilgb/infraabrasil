# VETOR-CHAT-12.3 — Homologação Completa e Preparação para Ativação

## Objetivo

Concluir os testes pendentes da CHAT-12.2 e preparar o atendimento automatizado do VetorOS para uso operacional controlado.

Priorizar correção e homologação das funcionalidades existentes. Não desenvolver novos módulos ou adicionar IA nesta fase.

## 1. Preparação do ambiente de testes

- Ler `correio.md`, `executed.md` e relatórios CHAT-11 a CHAT-12.2.
- Verificar o estado dos serviços publicados.
- Criar ambiente isolado para testes com dependências de desenvolvimento.
- Instalar Composer com dependências de teste somente nesse ambiente.
- Disponibilizar Node.js, TypeScript e ferramentas de revisão visual.
- Utilizar banco isolado, nunca o banco operacional.
- Preservar configurações e dados existentes.

Não instalar PHPUnit, Pest ou dependências de desenvolvimento nos containers produtivos.

## 2. Executar testes pendentes

Executar:

- `SupportAutomationTest.php`.
- `SupportMultichannelTest.php`.
- Regressão completa do VetorLeads.
- PHPStan.
- TypeScript.
- ESLint.
- Build Vite.
- Testes de concorrência e isolamento multitenant.

Corrigir os problemas encontrados e repetir os testes afetados.

## 3. Homologar as 14 respostas

Verificar individualmente:

- Correspondência das intenções.
- Prioridade das regras.
- Ausência de respostas conflitantes.
- Separação por canal.
- Horários e fuso.
- Transferência humana.
- Retorno à automação.
- Cancelamento.
- Opt-out.
- Histórico e auditoria.

Confirmar que as regras inativas não respondem ao público.

Revisar os textos de acordo com as funcionalidades efetivas do VetorOS, mantendo preços e condições comerciais sujeitos à confirmação.

## 4. Simulação completa WebChat e WhatsApp

Executar jornadas com identidades de teste isoladas.

Validar a sequência:

Mensagem recebida → identificação → intenção → resposta → qualificação → CRM → atendimento humano → encerramento.

No WhatsApp, testar adicionalmente:

- Sessão e tenant.
- HMAC.
- Replay.
- Outbox.
- Falha de envio.
- Suspensão por operador.
- Recuperação.
- Prevenção de duplicidade.

Manter envio real desativado.

## 5. Horários e configuração

Adicionar configurações administrativas claras para:

- Fuso horário.
- Dias de atendimento.
- Horário inicial e final.
- Mensagem fora do expediente.
- Encaminhamento ao operador.

Não presumir o horário comercial da empresa. Caso não exista horário definido, manter as regras dependentes de expediente inativas até configuração pelo administrador.

## 6. Revisão visual

Inspecionar com Chromium:

- Central de Atendimento.
- Automação de Atendimento.
- Cadastro e edição de regras.
- Simulador.
- Checklist.
- Indicadores.
- Conversas WebChat.
- Conversas WhatsApp.

Testar desktop e celular.

Corrigir transbordamentos, informações duplicadas, controles confusos e problemas de responsividade.

## 7. Preparação operacional

Após aprovação dos testes:

- Confirmar tenant correto.
- Confirmar regras revisadas.
- Confirmar permissões dos operadores.
- Confirmar sessão WAHA.
- Confirmar backup.
- Confirmar mecanismo para desativação imediata.
- Gerar relatório de prontidão.

Não ativar WhatsApp globalmente.

Não ativar automaticamente as 14 regras em produção.

Não enviar mensagens reais sem autorização explícita.

## 8. Versionamento

Auditar o Git dos projetos envolvidos.

Identificar alterações pendentes, arquivos sensíveis e diferenças entre os repositórios e as imagens publicadas.

Preparar commits organizados sem descartar mudanças preexistentes.

Não executar push sem autorização.

## 9. Relatório obrigatório

Atualizar `executed.md` com:

1. Testes executados e resultados.
2. Falhas encontradas e corrigidas.
3. Homologação das 14 respostas.
4. Evidências desktop e celular.
5. Resultado dos testes multitenant.
6. Estado das configurações de horário.
7. Situação do WhatsApp.
8. Estado do Git.
9. Pendências reais para ativação operacional.

**Critério de conclusão:** todos os testes automatizados devem passar, as 14 respostas precisam ser revisadas, a jornada sintética multicanal deve funcionar e a interface precisa estar validada. A homologação real com WhatsApp somente poderá ser declarada após teste autorizado com mensagem real.
