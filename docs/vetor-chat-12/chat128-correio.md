# VETOR-CHAT-12.8 — Finalização do Laboratório e Homologação do Widget VetorOS

## Objetivo

Finalizar o Laboratório de Atendimento implementado na CHAT-12.7, conectando-o de forma segura ao widget real do VetorOS e concluindo a homologação técnica e visual.

Não reconstruir o laboratório, o CRM ou o motor de atendimento.

## 1. Auditoria direcionada

- Ler `correio.md`, `executed.md` e o relatório CHAT-12.7.
- Identificar os arquivos implementados e as pendências documentadas.
- Verificar as alterações acumuladas nos dois repositórios.
- Preservar todas as funcionalidades existentes.

## 2. Ponte de homologação do widget

Implementar uma sessão temporária de homologação que conecte o widget VetorOS ao Laboratório VetorLeads.

Requisitos:

- Emissão da credencial exclusivamente por administrador autenticado e autorizado.
- Token aleatório, de alta entropia, com hash armazenado no servidor.
- Vinculação ao tenant, usuário administrador e sessão de laboratório.
- Validade curta, expiração e revogação.
- Escopo exclusivo de sandbox.
- Proteção contra reutilização indevida.
- Limitação de requisições.
- Proibição de acesso a regras de outro tenant.

O widget em modo de homologação deverá consumir exclusivamente os endpoints do laboratório.

Não permitir fallback automático para endpoints operacionais se a credencial for inválida, expirada ou revogada.

Não utilizar parâmetros públicos simples para ativar testes.

## 3. Conversa completa pelo widget

Validar:

1. Abertura do widget de homologação.
2. Envio de mensagem.
3. Correspondência de intenção.
4. Resposta da regra selecionada.
5. Continuidade do contexto.
6. Qualificação simulada.
7. Prévia de oportunidade.
8. Transferência humana simulada.
9. Resposta de operador.
10. Retorno ao automático.
11. Encerramento.
12. Expiração e revogação da sessão.

Nenhuma operação deverá criar lead, oportunidade, mensagem operacional ou outbox real.

O widget público normal deverá permanecer inalterado em seu comportamento.

## 4. Revisão das 14 regras

Revisar individualmente:

- Conteúdo e clareza.
- Expressões de reconhecimento.
- Prioridade.
- Conflitos entre regras.
- Informações de funcionalidades.
- Informações comerciais.
- Encaminhamento humano.
- Mensagens fora do expediente.

Manter a afirmação sobre formas de pagamento condicionada à confirmação comercial.

Não inventar horários de atendimento.

Registrar o resultado da revisão de cada regra.

## 5. Dependências e validação VetorOS

Preparar dependências locais reproduzíveis usando os lockfiles existentes.

Executar os comandos corretos definidos no projeto para:

- Verificação TypeScript.
- Testes JavaScript.
- Lint, quando disponível.
- Build de produção.

Corrigir a execução dos testes TypeScript no Node utilizado, sem atualizar dependências indiscriminadamente.

Não instalar dependências de desenvolvimento nos containers produtivos.

## 6. Chromium desktop e celular

Executar revisão visual em 1440 px e 390 px.

Verificar:

- Laboratório.
- Lista de regras.
- Seleção de canal.
- Conversa simulada.
- Qualificação.
- Transferência humana.
- Widget VetorOS em homologação.
- Expiração da credencial.
- Estados vazios e mensagens de erro.

Salvar capturas e relatório de erros JavaScript.

## 7. Regressão e segurança

Executar:

- Regressão PHP completa.
- Testes do laboratório.
- Testes da ponte autenticada.
- Isolamento multitenant.
- Proteção contra escrita comercial.
- Ausência de envios externos.
- PHPStan.
- TypeScript e ESLint.
- Builds dos dois produtos.

Testar que uma credencial inválida ou revogada não provoca nenhuma chamada operacional.

Utilizar banco isolado e as proteções da CHAT-12.5.

## 8. Preparação de publicação

Documentar:

- Migration do laboratório.
- Ordem de publicação dos dois sistemas.
- Necessidade de atualização coordenada do widget e das APIs.
- Plano de reversão seguro.
- Identificação de commits e imagens.
- Preservação das sessões existentes.

Não executar deploy, commit ou push automaticamente.

Manter todas as regras produtivas inativas e WhatsApp automático desligado.

## 9. Relatório

Atualizar `executed.md` com:

- Implementações concluídas.
- Testes e resultados.
- Revisão das 14 regras.
- Evidências do widget.
- Capturas desktop/celular.
- Estado Git.
- Pendências reais.
- Procedimento de publicação.

**Critério de conclusão:** laboratório e widget integrados com sessão autenticada e temporária; jornada completa aprovada; ausência comprovada de efeitos operacionais; testes e builds dos dois produtos aprovados.

Não declarar homologação pública ou WhatsApp real nesta fase.
