# FISCAL-SPEDY-04 — Administração central no RootAdmin

**Projeto:** `infra-abrasil/gateway/vetoros`

## Objetivo

Centralizar toda a administração da integração Spedy no painel RootAdmin, preservando a experiência de emissão fiscal nativa do VetorOS.

A implementação FISCAL-SPEDY-03.3 já existe. Reutilizar os serviços, models, controllers e migrations atuais, evitando duplicações e regressões.

## 1. Criar seção RootAdmin → Fiscal

Implementar uma área administrativa exclusiva:

`/admin/fiscal`

Organizar em abas:

### Integração Spedy
- Ambiente sandbox/produção.
- Estado de conexão com a API.
- Credencial principal da plataforma, somente escrita e mascarada.
- Configuração e verificação do webhook.
- Diagnóstico de conexão, sem emissão.
- Registro seguro dos erros de comunicação.

Não exibir segredos em logs, JSON, HTML ou props Inertia.

### Empresas emissoras
- Listar empresas cadastradas no VetorOS.
- Identificar CNPJ, nome e situação fiscal.
- Habilitar ou bloquear emissão individualmente.
- Liberar NF-e, NFC-e e NFS-e por empresa.
- Consultar status do cadastro Spedy.
- Consultar certificado e sua validade.
- Identificar empresas com configuração incompleta.
- Exibir a situação de homologação antes de permitir emissão real.

### Monitoramento
- Notas emitidas, autorizadas, rejeitadas e canceladas.
- Quantidade de documentos por empresa.
- Falhas de integração.
- Documentos pendentes de reconciliação.
- Histórico de operações.
- Filtros por período, empresa e modelo fiscal.

Preparar indicadores de utilização para uma futura política comercial, **sem implementar cobrança por nota**.

## 2. Responsabilidades do cliente

Em `Sistema → Configurações fiscais`, manter somente:

- Dados fiscais da empresa.
- Certificado digital A1.
- Regime tributário.
- Inscrição estadual e municipal.
- Informações fiscais necessárias.
- CSC e identificador da NFC-e.
- Preferências fiscais permitidas.
- Consulta do status de habilitação.
- Emissão e gerenciamento dos próprios documentos.

O cliente não poderá:

- Alterar credenciais centrais da Spedy.
- Trocar o ambiente global da plataforma.
- Cadastrar unilateralmente emissores na conta Spedy.
- Habilitar emissão sem liberação do RootAdmin.
- Visualizar notas ou dados de outra empresa.

O cadastro remoto do emitente deverá ser comandado pelo RootAdmin ou por processo administrativo central explicitamente autorizado, depois da configuração e validação dos dados fornecidos pelo cliente.

## 3. Segurança e persistência

- Reaproveitar `config/services.php` e a configuração fiscal existente.
- Não criar duas fontes independentes para a API Key.
- Caso as credenciais passem a ser administradas pelo banco, usar armazenamento criptografado, com procedimentos seguros de rotação e precedência claramente definida.
- Nunca disponibilizar a chave titular Spedy em instalações on-premise ou no frontend.
- Garantir separação entre empresas.
- Registrar auditoria de habilitação, bloqueio, troca de ambiente e alterações críticas.
- Exigir confirmação adicional para operações sensíveis.
- Restringir rotas no backend exclusivamente ao RootAdmin.
- Evitar chamadas à API durante simples consultas às telas.
- Desabilitar emissão por padrão enquanto a configuração estiver incompleta.

## 4. Preservar o fiscal existente

Manter:

- NF-e nas vendas.
- NFC-e no PDV.
- NFS-e nas ordens de serviço.
- Webhooks e sincronização.
- Cancelamentos.
- Consultas e downloads de PDF/XML.
- Registros manuais existentes.

Não implementar planos fiscais, franquias nem preços por nota.

## 5. Testes

Adicionar cobertura para:

- RootAdmin acessando a configuração central.
- Administrador de tenant recebendo 403.
- Cliente comum recebendo 403.
- Isolamento entre empresas.
- Segredos ausentes das respostas e logs.
- Liberação e bloqueio efetivo da emissão.
- Webhooks e reconciliação preservados.
- Falhas da Spedy sem vazamento de informações.
- Nenhuma regressão na emissão fiscal.

Executar a suíte e validar as migrations em MySQL 8.4 descartável.

Não realizar emissão fiscal real ou deploy.

## 6. Entrega

Registrar em `executed.md`:

- Estrutura implementada.
- Arquivos alterados.
- Fluxos de permissões.
- Resultados dos testes.
- Pendências de homologação.

Criar commit local específico e preservar as demais alterações.


# FISCAL-SPEDY-04 — ADENDO: Notas fiscais do próprio SaaS

Acrescentar ao escopo anterior a emissão de notas fiscais da própria plataforma VetorOS, exclusivamente pelo RootAdmin.

## 1. RootAdmin → Fiscal → Notas do SaaS

Implementar funcionalidade que permita ao RootAdmin:

- Selecionar o cliente contratante.
- Consultar a assinatura e o plano contratado.
- Identificar o período de referência.
- Consultar o valor efetivamente cobrado.
- Emitir manualmente uma NFS-e da ABrasil Sistemas.
- Consultar situação da autorização.
- Visualizar, baixar e enviar o documento fiscal por e-mail.
- Consultar notas anteriores do cliente.
- Solicitar cancelamento conforme as regras aplicáveis.

## 2. Configuração do emitente

A ABrasil Sistemas será cadastrada como empresa emissora própria da plataforma, com CNPJ, inscrição municipal, município, regime tributário e certificado, quando exigidos.

O cadastro fiscal da plataforma será independente dos dados das empresas clientes.

Reutilizar a infraestrutura Spedy com credenciais próprias do emitente, respeitando a arquitetura de segurança existente.

## 3. Integração com assinaturas

Buscar o valor real da contratação ou cobrança, não o preço público de tabela.

Preservar os períodos:

- Mensal.
- Semestral.
- Anual.

O documento deverá refletir o serviço efetivamente prestado e a operação financeira correspondente.

Não emitir automaticamente pelo simples fato de existir uma assinatura ou pelo vencimento de uma cobrança.

Prevenir notas duplicadas para a mesma operação faturada, permitindo ajustes ou substituições somente nos fluxos legalmente previstos.

## 4. Segurança

- Acesso exclusivo ao RootAdmin.
- Não utilizar o tenant do cliente como emitente.
- Não expor credenciais fiscais.
- Não misturar notas do SaaS com notas operacionais dos clientes.
- Manter auditoria de todas as ações.
- Aplicar verificações de autorização antes de cada operação fiscal.

## 5. Envio ao cliente

Permitir encaminhamento por e-mail dos documentos autorizados, com registro de data, destinatário e resultado do envio.

Falhas no envio de e-mail não devem provocar uma segunda emissão fiscal.

## 6. Validação

Criar testes para emissão, isolamento de emitentes, vínculo financeiro, valores, duplicidade, cancelamento, falhas de comunicação e entrega por e-mail.

Validar a estrutura no MySQL 8.4 descartável.

Usar mocks na implementação. Não emitir notas reais nem realizar deploy sem autorização.

Registrar em `executed.md` e criar commit local específico.
