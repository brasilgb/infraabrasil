# VETOR-CHAT-12.4 — Correção da Regressão e Proteção do Banco Operacional

## Objetivo

Corrigir as pendências da CHAT-12.3, impedir definitivamente que testes automatizados alcancem o banco operacional e concluir a homologação técnica do atendimento multicanal VetorLeads/VetorOS.

Não desenvolver novas funcionalidades comerciais nesta fase.

## 1. Auditoria prioritária do banco operacional

Antes de executar qualquer teste:

- Confirmar o banco operacional utilizado pelo VetorLeads.
- Verificar migrations, tenants, regras, leads, prospects, conversas, mensagens, decisões e outbox.
- Inspecionar possíveis registros e alterações produzidos durante o incidente da CHAT-12.3.
- Comparar o estado atual com backups e evidências anteriores.
- Identificar alterações legítimas ocorridas depois do backup.
- Registrar divergências e o grau de certeza das conclusões.

Executar a auditoria inicialmente somente com consultas de leitura.

Não apagar registros, restaurar backups ou modificar dados comerciais sem comprovar a necessidade e preservar alterações legítimas.

Se não for possível determinar se houve alterações, documentar explicitamente a incerteza.

## 2. Isolamento definitivo dos testes

Corrigir a arquitetura de testes para impedir acesso acidental à produção.

Implementar:

- Configuração exclusiva para ambiente de teste.
- Bloqueio de nomes de bancos operacionais.
- Validação da conexão antes de executar migrations, seeds ou testes.
- Credenciais de teste sem permissão de escrita no banco operacional.
- Testes em SQLite ou MariaDB/MySQL isolado.
- Verificação explícita do ambiente nos comandos de teste.

A proteção não pode depender exclusivamente do arquivo `phpunit.xml`.

Criar teste automatizado que demonstre que uma execução configurada acidentalmente para o banco operacional é recusada antes de qualquer escrita.

Não executar esse teste de proteção contra uma conexão operacional com permissão de escrita.

## 3. Investigar as 27 falhas

Classificar e corrigir os erros registrados:

- CustomerOnboarding: 3.
- Prospecting: 21.
- TenantCommercialIsolation: 1.
- TrialExpiredAutomation: 2.

Para cada grupo:

1. Identificar a causa.
2. Determinar se é regressão de código, isolamento de testes ou diferença do ambiente.
3. Corrigir a causa, não apenas a expectativa dos testes.
4. Executar os testes do grupo.
5. Registrar o resultado e os arquivos alterados.

Não desabilitar testes para produzir aprovação artificial.

## 4. Estabilizar as dependências

Preparar ambiente de testes reproduzível e isolado, com:

- Dependências Composer de desenvolvimento.
- Pest/PHPUnit.
- TypeScript.
- ESLint.
- PHPStan.
- Chromium/Playwright quando disponível.

Preferir o ambiente local de desenvolvimento ou um ambiente temporário separado da produção.

Não instalar dependências de desenvolvimento no container produtivo.

Garantir que o processo de instalação e execução possa ser repetido sem depender de volumes temporários inconsistentes.

## 5. Regressão completa

Executar novamente:

- SupportAutomationTest.
- SupportMultichannelTest.
- Testes comerciais.
- Testes de prospecção.
- Testes de isolamento multitenant.
- Regressão completa.
- PHPStan.
- TypeScript.
- ESLint global.
- Build Vite.

Corrigir os cinco problemas de ESLint da extensão Firefox, preservando seu comportamento.

Não declarar aprovação se houver testes pendentes ou falhas.

## 6. Homologação visual

Utilizar Chromium/Playwright em ambiente isolado.

Validar desktop e celular:

- Dashboard.
- Central de Atendimento.
- Automação.
- Editor das regras.
- Simulador.
- Checklist de prontidão.
- Conversas WebChat e WhatsApp.
- Contatos recebidos.

Verificar responsividade, navegação, estados vazios, formulários e mensagens de erro.

## 7. Reconferência operacional

Após estabilizar os testes, consultar em modo somente leitura:

- Tenant responsável pelo VetorOS.
- Quantidade e estado das 14 regras.
- Configuração de horário e fuso.
- Situação dos operadores.
- Sessão WAHA.
- Estado da outbox.
- Trava global do WhatsApp.
- Saúde de aplicação, worker e scheduler.

Não ativar as regras.

Manter `SUPPORT_AUTOMATION_WHATSAPP_DELIVERY_ENABLED=false`.

## 8. Versionamento

Auditar alterações locais acumuladas em VetorLeads e VetorOS.

Preparar um inventário de diferenças entre Git, código local e imagens publicadas.

Não descartar alterações anteriores.

Não executar commit, push ou deploy sem autorização.

## 9. Entrega

Atualizar `executed.md` com:

1. Resultado da auditoria do banco operacional.
2. Evidência de isolamento seguro dos testes.
3. Diagnóstico individual dos 27 testes.
4. Correções realizadas.
5. Resultado da regressão completa.
6. Resultado da revisão visual.
7. Situação das regras e canais.
8. Arquivos modificados.
9. Pendências remanescentes.
10. Estado final da infraestrutura.

**Critério obrigatório:** nenhuma execução de testes pode escrever no banco operacional. A regressão deve passar integralmente em ambiente isolado antes de avançar para a homologação real.

Caso alguma falha permaneça, registrar o bloqueio e não declarar a fase aprovada.
