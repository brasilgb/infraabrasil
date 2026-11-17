# VETORCHAT-SAAS-09 — CONTINUAÇÃO OPERACIONAL

A instrução VETORCHAT-SAAS-09 já teve seus commits e pushes executados manualmente.

O código foi atualizado na VPS `/opt/infra-abrasil`.

**Não reiniciar a etapa 09 nem repetir operações concluídas.**

## Objetivo

Concluir os deploys pendentes, individualmente, e encerrar a homologação operacional.

## Procedimento

1. Ler o estado atual dos containers, commits e relatórios.
2. Identificar quais serviços já receberam novas imagens e migrations.
3. Confirmar o resultado da publicação do VetorLeads.
4. Se o VetorLeads estiver saudável, prosseguir com ABrasil Sistemas.
5. Depois publicar VetorOS.
6. Finalmente, publicar VetorPet, após validar as dependências exigidas.
7. Não alterar Desgarrados.
8. Não reiniciar serviços de infraestrutura desnecessariamente.
9. Preservar bancos, volumes, sessões WAHA e configurações existentes.
10. Antes de migrations ou substituição de imagens, confirmar backups e rollback.

## Homologação

Verificar HTTP, sitemap, robots.txt, widget.js, APIs, inbox, isolamento de tenants e estado WAHA.

Executar comunicação ponta a ponta apenas com dados e identidades de teste autorizados.

Não repetir testes locais já aprovados, exceto quando modificações posteriores justificarem sua execução.

Se algum serviço apresentar problema crítico, interromper somente sua publicação, preservando os demais.

## Relatório final

Atualizar `executed.md` com o estado efetivo de cada serviço:

- Commit.
- Imagem em execução.
- Migrations aplicadas.
- Resultado HTTP.
- Resultado operacional.
- Pendências comprovadas.

Classificar separadamente cada produto como publicado, publicado com pendências, não publicado ou bloqueado.

**Prioridade: concluir a publicação do que já está implementado, sem criar novas etapas de desenvolvimento.**
