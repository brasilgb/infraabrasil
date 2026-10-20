# VETOR-CHAT-12.5 — Recuperação Definitiva dos Testes e Homologação

## Objetivo

Recuperar o ambiente de desenvolvimento e homologação do VetorLeads, resolver as 27 falhas anteriores e concluir a validação técnica do atendimento automatizado.

Esta fase é de estabilização. Não acrescentar funcionalidades comerciais ou reconstruir módulos existentes.

## 1. Identificação do ambiente

Antes de qualquer execução:

- Identificar se o Codex está operando na VPS ou na estação de desenvolvimento.
- Localizar a raiz real do repositório VetorLeads.
- Verificar PHP, Composer, Node.js, MariaDB e dependências disponíveis.
- Identificar o motivo das instalações Composer temporárias estarem incompletas.
- Verificar permissões, espaço em disco e configuração de rede.
- Registrar o ambiente escolhido para os testes.

Priorizar a estação local de desenvolvimento.

Não utilizar containers produtivos para testes.

## 2. Recuperação das dependências

No ambiente isolado:

- Executar instalação Composer completa com dependências de desenvolvimento.
- Utilizar o composer.lock existente.
- Não executar composer update desnecessariamente.
- Instalar dependências JavaScript com npm ci.
- Confirmar a existência de Pest, PHPUnit, PHPStan, TypeScript e ESLint.
- Validar o autoload e a inicialização da aplicação.
- Corrigir eventuais problemas de permissões e diretórios temporários.

Não utilizar mounts ou diretórios temporários que desapareçam durante os testes.

Não alterar dependências da aplicação produtiva.

## 3. Proteção do banco operacional

Revisar os mecanismos criados na CHAT-12.4.

Implementar defesa em camadas:

- Bloqueio de execução quando APP_ENV não for testing.
- Recusa explícita do banco operacional vetorleads.
- Banco exclusivo de testes.
- Credenciais próprias para os testes.
- Nenhuma permissão das credenciais de teste no banco operacional.
- Proibição de conexões de teste aos hosts operacionais.
- Validação antes de migrations, seeds e RefreshDatabase.
- Recusa de DB_URL ou configurações alternativas não autorizadas.

Verificar que a política é aplicada também aos testes executados diretamente pelo Pest e PHPUnit.

Demonstrar a recusa de configurações inseguras por meio de teste sem conexão real com produção.

Não confiar somente no nome do banco.

## 4. Investigar e resolver as 27 falhas

Executar inicialmente os grupos:

- CustomerOnboarding — 3 falhas.
- Prospecting — 21 falhas.
- TenantCommercialIsolation — 1 falha.
- TrialExpiredAutomation — 2 falhas.

Registrar a causa de cada grupo.

Corrigir problemas reais de código, configurações ou isolamento.

Não alterar assertions para esconder falhas.

Não desabilitar testes.

Após as correções, executar cada grupo individualmente.

## 5. Homologação automatizada completa

Executar:

- TestDatabaseSafetyTest.
- SupportAutomationTest.
- SupportMultichannelTest.
- Testes de prospecção.
- Testes comerciais.
- Testes multitenant.
- Regressão PHP completa.
- PHPStan.
- TypeScript.
- ESLint global.
- Build Vite.

Não declarar sucesso se alguma etapa não for executada ou permanecer com falha.

## 6. Revisão visual

Preparar Chromium/Playwright no ambiente isolado.

Executar jornadas em desktop e celular:

- Dashboard.
- Captura de prospects.
- CRM.
- Central de Atendimento.
- Automação de Atendimento.
- Editor de regras.
- Simulador.
- Contatos recebidos.
- Configurações.

Utilizar dados sintéticos e identidade de teste.

Corrigir erros JavaScript, transbordamentos, problemas de navegação e defeitos funcionais identificados.

## 7. Homologação multicanal

Validar integralmente com simuladores:

- WebChat.
- WAHA.
- Regras por canal.
- Qualificação.
- Memória contextual.
- Transferência humana.
- Retorno à automação.
- Outbox.
- Reconciliação de entregas.
- Opt-out.
- Isolamento multitenant.

Não enviar mensagens reais.

Não ativar as 14 regras produtivas.

## 8. Evidências operacionais

Na VPS, realizar somente consultas e verificações não destrutivas.

Confirmar:

- Saúde dos serviços.
- Tenant responsável.
- Regras existentes.
- Travas de ativação.
- Estado da sessão WAHA.
- Situação dos operadores.
- Quantidade de mensagens pendentes.
- Ausência de disparos não autorizados.

Não publicar novas imagens antes de concluir os testes.

## 9. Versionamento

Auditar o código acumulado desde CHAT-11.

Separar alterações por finalidade.

Identificar arquivos temporários e segredos que não devem entrar no Git.

Preparar versionamento reproduzível.

Não executar commit ou push sem autorização.

## 10. Relatório

Atualizar executed.md incluindo:

1. Ambiente utilizado.
2. Instalação das dependências.
3. Evidência do bloqueio de banco operacional.
4. Diagnóstico das 27 falhas.
5. Correções aplicadas.
6. Quantidade de testes aprovados.
7. Resultado das análises estáticas.
8. Evidências visuais.
9. Homologação multicanal.
10. Pendências restantes.

**Critério de conclusão:** ambiente reproduzível, banco operacional protegido, regressão integral aprovada, revisão visual concluída e jornadas multicanais homologadas com dados sintéticos.

Se uma dependência impedir os testes, identificar e resolver sua causa antes de encerrar a execução. Não repetir tentativas sem diagnóstico.
