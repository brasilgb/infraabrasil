# VETOROS-WAHA-FIX-01 — Corrigir conexão WhatsApp

## Objetivo
Investigar e corrigir a falha de conexão WhatsApp apresentada em Configurações → Mensagens WhatsApp do VetorOS.

**Erro apresentado:** "Não foi possível conectar. Verifique o serviço do WAHA e tente novamente."

## 1. Diagnóstico obrigatório

- Verificar o estado do serviço WAHA na VPS.
- Conferir a conectividade entre o container VetorOS e o WAHA.
- Verificar URL interna do serviço, porta, API Key, autenticação e sessão configurada.
- Consultar logs da API, do VetorOS e do WAHA.
- Verificar incompatibilidade entre endpoints utilizados pelo VetorOS e a versão instalada do WAHA.
- Identificar se o botão "Conectar WhatsApp" está chamando o endpoint correto.
- Identificar se o erro ocorre no backend ou no frontend.

Não presumir que o WAHA esteja indisponível apenas pela mensagem da interface.

## 2. Correção

Corrigir a causa identificada, preservando:

- Integração existente do VetorChat/VetorLeads.
- Sessões WhatsApp já configuradas.
- Isolamento entre tenants.
- Configurações e credenciais existentes.
- Histórico de mensagens.

Não criar uma nova instalação WAHA nem alterar sessões existentes sem necessidade comprovada.

## 3. Interface

Aprimorar a tela de configuração WhatsApp para exibir estados reais:

- Conectado.
- Desconectado.
- Aguardando leitura do QR Code.
- Inicializando sessão.
- Erro de autenticação.
- Serviço WAHA indisponível.

O botão "Conectar WhatsApp" deverá abrir o fluxo de conexão, apresentando o QR Code quando necessário.

Adicionar ações de atualizar status e desconectar com confirmação.

Nunca exibir chaves ou tokens na interface.

## 4. Testes

Executar testes funcionais do fluxo de conexão, consulta de status e tratamento de erros.

Validar o funcionamento sem prejudicar as integrações existentes.

Não executar alterações em containers locais. Utilizar testes de código e os serviços existentes na VPS quando necessário.

Não insistir repetidamente em ferramentas de teste que estejam indisponíveis.

## 5. Publicação

Após identificar e corrigir o problema, executar os testes viáveis e preparar a publicação.

Alterações operacionais na VPS devem preservar os demais serviços, sem reinicialização geral da infraestrutura ou exclusão de sessões.

## 6. Relatório

Registrar em `executed.md`:

- Causa raiz.
- Arquivos alterados.
- Configuração corrigida.
- Testes executados e resultados.
- Estado real da conexão WhatsApp.
- Pendências, se existirem.

Priorizar a correção funcional. Não ampliar o escopo para refatorações desnecessárias.


# VETORCHAT-NATIVE-01 — Chat nativo do VetorLeads e correção da indisponibilidade

## Objetivo

Corrigir a indisponibilidade do chat no VetorLeads e tornar o WebChat um recurso nativo do produto, sem depender da configuração de widget externo, domínio autorizado ou instalação manual para seu próprio funcionamento.

Preservar a arquitetura multitenant do VetorChat e a integração compartilhada com o WAHA.

## 1. Diagnosticar a falha atual

Investigar:

- Por que o chat não está disponível no VetorLeads.
- Se há conflito entre domínio do VetorLeads e domínio do widget.
- Se a validação de domínio, chave pública ou tenant está bloqueando a instalação.
- Se o próprio VetorLeads está tentando consumir seu widget como uma integração externa.
- Se existem problemas com cookies, CORS, CSP, autenticação, sessões, API ou proxy.
- Se o carregamento do WebChat está indevidamente condicionado ao estado do WAHA.

Identificar a causa real antes de aplicar alterações.

## 2. Tornar o chat nativo

Implementar o WebChat como funcionalidade própria do VetorLeads.

Requisitos:

- Chat disponível automaticamente na landing page do VetorLeads.
- Tenant institucional resolvido internamente pelo backend.
- Não exigir cadastro manual do domínio do próprio produto.
- Não exigir geração ou instalação manual de widget para o VetorLeads.
- Não depender de WAHA para mensagens WebChat.
- Reutilizar componentes, APIs e regras de negócio existentes.
- Manter a identificação do produto de origem como `vetorleads`.
- Preservar isolamento multitenant e segurança.

A interface pública poderá utilizar o mesmo componente visual do widget externo, mas com inicialização interna segura.

Não criar um segundo motor de mensagens nem duplicar bancos, conversas ou lógica de atendimento.

## 3. Central de atendimento no painel

Verificar e ajustar a central de conversas existente para oferecer:

- Conversas recebidas pelo WebChat.
- Identificação do produto de origem.
- Nome, telefone e WhatsApp do visitante, quando informados.
- Histórico de mensagens.
- Associação ao CRM.
- Resposta humana.
- Resposta automática, quando habilitada.
- Transferência para atendente.
- Indicadores de mensagens não lidas.

A central deve estar disponível ao tenant sem exigir configuração de instalação de widget.

## 4. Preservar widget externo

O widget externo continuará funcionando normalmente para:

- ABrasil Sistemas.
- VetorOS.
- VetorSales.
- Outros produtos.
- Sites de tenants externos.

Preservar a arquitetura existente:

- Tenant ABrasil compartilhado entre seus produtos.
- Identificação independente do produto de origem.
- Chaves próprias para outros tenants.
- Isolamento de conversas e dados por tenant.

Não modificar contratos públicos da API sem compatibilidade.

## 5. WhatsApp e WAHA

Manter separação entre os canais:

**WebChat:** funcionamento nativo, independente do WAHA.

**WhatsApp:** integração com WAHA quando configurada e disponível.

Uma falha no WAHA não pode impedir a abertura do chat web, o recebimento de mensagens ou o atendimento humano.

Não criar sessões WAHA automaticamente nem utilizar credenciais de outro tenant.

## 6. Experiência visual

Adicionar o botão flutuante de chat no canto inferior direito da landing page do VetorLeads.

Utilizar ícone de conversa, design profissional, responsivo e consistente com o produto.

Evitar duplicidade de botões ou widgets.

Dentro do painel, utilizar a central de atendimento já existente, corrigindo interfaces incompletas ou confusas, sem adicionar outro widget desnecessário.

## 7. Testes e publicação

Testar os fluxos essenciais:

1. Visitante abre o chat público do VetorLeads.
2. Visitante envia uma mensagem.
3. Mensagem aparece na central do tenant correto.
4. Atendente responde e visitante recebe a resposta.
5. Conversa é associada ao CRM corretamente.
6. Chat funciona com WAHA indisponível.
7. Widget externo continua funcionando no VetorOS e na ABrasil.
8. Tenant externo não acessa conversas de outro tenant.

Não implementar em containers locais.

Usar testes automatizados disponíveis. Caso o navegador de testes não funcione, registrar a limitação e executar testes alternativos viáveis, sem insistir indefinidamente.

Corrigir e publicar mediante validação dos fluxos críticos, preservando os dados existentes.

Registrar causa raiz, alterações e resultados em `executed.md`.

**Prioridade: disponibilizar o chat funcional, não iniciar outra rodada de refatorações ou auditorias sem necessidade.**
