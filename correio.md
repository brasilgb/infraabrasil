# VETORLEADS-PROSPECT-08 — Preparação e homologação comercial

## Objetivo

Consolidar o trabalho das etapas anteriores e preparar o VetorLeads para operação comercial real, verificando o fluxo completo de prospecção.

Não ampliar o escopo com funcionalidades desnecessárias.

## 1. Consolidação dos repositórios

- Auditar alterações locais e os 11 commits à frente do remoto no VetorLeads.
- Verificar as alterações do VetorOS e da infraestrutura compartilhada.
- Preservar as implementações anteriores, inclusive landing page, CRM, atendimento, WebChat e funcionalidades dos demais produtos.
- Examinar as duas migrations pendentes e suas dependências.
- Identificar possíveis conflitos entre código local, runtime de produção e infraestrutura.
- Organizar alterações em commits coerentes, sem descartar trabalho.
- Não executar push nem deploy sem autorização.

## 2. Verificação da captura

Auditar a integração atual da extensão de prospecção com o VetorLeads.

Validar:
- Identificação e autenticação do tenant.
- Importação de empresas.
- Nome empresarial e atividade.
- Telefone, celular e possível WhatsApp.
- Website.
- Endereço, cidade e UF quando fornecidos.
- Origem e lote da captura.
- Tratamento de duplicidades.
- Recaptura e atualização conservadora.
- Tratamento de erros.

Não presumir que um telefone possui WhatsApp ativo.

Não inventar dados ausentes.

## 3. Fluxo comercial

Validar a jornada:

Captura → Lista de prospects → Qualificação → Seleção → Conversão para CRM → Acompanhamento no dashboard.

Verificar:
- Navegação intuitiva.
- Mensagens claras de sucesso e erro.
- Preservação de dados existentes.
- Deduplicação na conversão.
- Isolamento entre tenants.
- Histórico comercial.
- Indicadores calculados com dados reais, sem confundir conversão para CRM com vendas realizadas.

## 4. Qualificação

- Preservar score v3 e critérios por produto.
- Não executar requalificação em massa.
- Manter prévia e confirmação antes de persistir alterações.
- Verificar que produto ou segmento incorreto não produz uma oportunidade artificialmente favorável.
- Apresentar justificativas legíveis para a classificação.

## 5. Interface

Revisar as telas de captura, prospects, produtos, CRM e dashboard.

Manter padrão visual profissional, uniforme e responsivo.

A captura deve continuar totalmente independente das configurações administrativas da extensão.

Não duplicar menus, indicadores nem funcionalidades.

## 6. Homologação e segurança

Primeiro executar os testes completos em ambiente isolado.

Preparar um roteiro de teste real com uma pequena amostra autorizada de empresas, preservando todos os dados existentes.

Não realizar contatos ou disparos comerciais.

Nenhuma alteração persistente de registros reais deve ocorrer sem autorização específica.

Não executar migrations operacionais, push, deploy ou reinicialização de serviços nesta execução.

## 7. Relatório

Substituir integralmente `executed.md` com o relatório PROSPECT-08.

Documentar:

- Estado dos repositórios.
- Migrations e dependências.
- Fluxos efetivamente testados.
- Evidências de funcionamento.
- Problemas identificados e correções locais.
- Roteiro para publicação segura.
- Pendências para homologação real.
- Riscos e possíveis impactos sobre VetorOS, WebChat e infraestrutura.

**Critério de conclusão:** sistema tecnicamente consolidado, sem regressões conhecidas nos testes executados, com roteiro seguro de implantação e homologação operacional real claramente definido.
