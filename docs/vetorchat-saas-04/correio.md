# VETORCHAT-SAAS-04 — Unificação do widget, integração WAHA e atendimento multitenant por produto

## Objetivo

Consolidar o VetorChat como serviço central do VetorLeads, utilizando o mesmo widget em todos os produtos da ABrasil Sistemas, integrado ao WAHA e preparado para atendimento automatizado e humano.

Esta instrução substitui o escopo anterior da VETORCHAT-SAAS-04.

## 1. Arquitetura

O VetorLeads será o backend central de mensagens.

O ecossistema ABrasil deverá utilizar:

- Um único tenant ABrasil.
- Uma única configuração de widget compartilhada.
- Uma chave pública comum para instalação nos produtos autorizados.
- Uma conexão WAHA compartilhada, utilizando a estrutura já existente.
- Identificação obrigatória do produto de origem.
- Histórico e contexto separados por produto.
- Uma caixa de entrada central, com filtros por produto e canal.

Produtos iniciais:

- ABrasil Sistemas
- VetorOS
- VetorLeads
- VetorPet
- Outros produtos cadastrados futuramente.

Não criar tenants separados para esses produtos.

## 2. Identificação do produto

Implementar um cadastro de produtos/canais pertencentes ao tenant.

Cada produto deverá possuir:

- Identificador único.
- Nome e descrição.
- Domínios autorizados.
- Configuração visual opcional.
- Mensagem inicial.
- Base de conhecimento específica.
- Regras de atendimento.
- Atendentes responsáveis, quando aplicável.
- Estado ativo/inativo.

O script deverá continuar sendo o mesmo, podendo receber um identificador de produto.

Exemplo conceitual:

```html
<script
  src="https://vetorleads.com.br/widget.js"
  data-widget-key="CHAVE_PUBLICA_ABRASIL"
  data-product="vetoros"
  async>
</script>
```

O backend deve validar se o produto informado pertence ao tenant identificado pela chave e se o domínio solicitante está autorizado para esse produto.

O identificador de produto não deve permitir acesso a dados de outro tenant.

Para clientes externos, utilizar `generic` como produto padrão, permitindo personalização posterior.

## 3. Integração WAHA

Auditar a integração WAHA já existente na infraestrutura `/opt/infra-abrasil`, bem como o atendimento anteriormente desenvolvido para VetorOS.

Reutilizar a infraestrutura operacional existente sempre que possível.

Implementar um adaptador central WAHA no VetorLeads responsável por:

- Envio de mensagens.
- Recebimento de mensagens.
- Processamento de webhooks.
- Identificação de sessão.
- Associação com tenant, produto e conversa.
- Tratamento de mensagens duplicadas.
- Registro de falhas e tentativas.
- Reconciliação de estados e confirmações de entrega.
- Transferência entre atendimento automático e humano.

Não criar uma segunda instalação WAHA desnecessariamente.

A infraestrutura WAHA pode ser compartilhada fisicamente entre clientes, mas as sessões, credenciais e permissões de acesso devem possuir isolamento por tenant.

O tenant ABrasil utilizará a sessão compartilhada definida para seu ecossistema.

Tenants externos deverão cadastrar uma sessão ou conexão WAHA própria, utilizando seu respectivo telefone e configuração.

Credenciais privadas do WAHA nunca devem ser enviadas ao navegador.

## 4. Atendimento unificado

A mesma conversa deverá aparecer no painel VetorLeads com informações de:

- Tenant.
- Produto de origem.
- Canal: Webchat ou WhatsApp.
- Visitante/contato.
- Estado do atendimento.
- Responsável.
- Histórico e horários.
- Classificação comercial.

O operador deverá conseguir:

1. Receber mensagens do site.
2. Responder pelo painel.
3. Utilizar WhatsApp quando o contato possuir número válido e houver autorização para esse canal.
4. Consultar histórico relacionado ao atendimento.
5. Transferir para outro atendente.
6. Encerrar ou reabrir a conversa.
7. Criar ou vincular uma oportunidade comercial, mediante ação explícita ou regra autorizada.

Não duplicar conversas ou misturar identidades apenas porque o visitante possui nome semelhante.

## 5. Atendimento automatizado

Preparar o VetorChat para utilizar o mecanismo de respostas automáticas já existente no ecossistema, caso haja um componente funcional reaproveitável.

A automação deve usar:

- Tenant da conversa.
- Produto identificado.
- Canal utilizado.
- Base de conhecimento específica.
- Histórico pertinente.
- Regras comerciais.
- Instruções de transferência para atendente.

O atendimento do VetorOS deve responder sobre VetorOS; o atendimento da ABrasil deve responder sobre ABrasil.

Implementar regras para evitar respostas automáticas quando houver atendimento humano ativo, prevenir loops entre automações e registrar a origem das respostas.

Se não existir mecanismo de IA operacional, implementar somente a arquitetura e as regras determinísticas possíveis, registrando a integração de IA como pendência. Não simular respostas inteligentes.

## 6. Tenants externos

Cada tenant externo deverá possuir no painel:

- Configuração do widget.
- Chave pública própria.
- Domínios permitidos.
- Produto padrão.
- Configuração WAHA e status da sessão.
- Personalização do atendimento.
- Operadores autorizados.
- Histórico e métricas.
- Configuração de atendimento automático.

Nenhum tenant poderá consultar, enviar, receber ou administrar mensagens de outro tenant.

Uma instalação compartilhada de WAHA não poderá implicar acesso compartilhado às sessões.

## 7. Interface e experiência

Organizar a área Atendimento do VetorLeads com:

- Dashboard operacional.
- Caixa de entrada unificada.
- Filtros de produto, canal e status.
- Atendimentos aguardando resposta.
- Atendimento humano.
- Contatos recebidos.
- Configuração do widget.
- Configuração WhatsApp/WAHA.
- Produtos e canais.
- Regras de automação.

Evitar menus duplicados, excesso de configurações na mesma tela e informações técnicas desnecessárias para operadores.

Apresentar configurações avançadas em modais ou áreas específicas.

## 8. Segurança

Garantir:

- Isolamento por tenant.
- Autorização por produto.
- Validação de domínio.
- Proteção de credenciais privadas.
- Webhooks autenticados conforme recursos disponíveis no WAHA.
- Rate limiting.
- Idempotência de mensagens.
- Proteção contra envio duplicado.
- Auditoria de mensagens e configurações.
- Restrição de acesso ao histórico.
- Não assumir que `Origin` identifica clientes HTTP de maneira confiável.
- Proteção de dados pessoais e consentimento quando necessário.

A chave pública do widget não deve ser reutilizada como token de autenticação administrativa ou de acesso ao WAHA.

## 9. Testes

Executar testes automatizados com dados sintéticos cobrindo:

- Um tenant ABrasil com múltiplos produtos.
- Uma única chave pública funcionando para os produtos autorizados.
- Rejeição de produtos ou domínios não autorizados.
- Dois tenants externos independentes.
- Sessões WAHA separadas por tenant.
- Recebimento e envio bidirecional.
- Idempotência.
- Separação do histórico por produto.
- Transferência para atendimento humano.
- Ausência de vazamento entre tenants.
- Funcionamento desktop e mobile.

Validar com navegador quando o ambiente permitir.

Não insistir indefinidamente em ferramentas de navegador indisponíveis; utilizar alternativas confiáveis e registrar as limitações.

## 10. Restrições operacionais

- Não utilizar containers Docker no ambiente local.
- Não fazer deploy sem autorização.
- Não executar migrations em produção sem autorização.
- Não enviar mensagens reais.
- Não alterar sessões WAHA existentes sem planejamento.
- Não destruir ou sobrescrever dados.
- Preservar alterações preexistentes.
- Não criar serviços paralelos quando os existentes forem reutilizáveis.
- Não criar commits ou executar push sem autorização.

## 11. Entrega

Atualizar `executed.md` com:

- Comparação do correio.
- Auditoria do WAHA e do atendimento VetorOS existente.
- Arquitetura implementada.
- Arquivos alterados.
- Testes executados.
- Evidências do isolamento multitenant.
- Funcionamento por produto.
- Pendências para ativação na VPS.
- Riscos operacionais.
- Plano de publicação e homologação.

Resultado esperado: um único VetorChat reutilizável por produto, integrado ao WAHA, com atendimento centralizado no VetorLeads e isolamento completo dos clientes externos.
