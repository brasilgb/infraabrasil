# VETORCHAT-SAAS-05.3 — Consolidação de versionamento

O superprojeto `infra-abrasil` está na branch `main`, commit `0f6665e`, com o rebase encerrado e os conflitos resolvidos.

## Objetivo

Auditar as alterações locais pendentes, preservar os trabalhos de SEO, VetorChat e relatórios, e preparar o versionamento organizado dos produtos.

## Instruções

1. Confirmar que não existem conflitos Git ou operações de rebase pendentes.
2. Auditar individualmente os submódulos ABrasil Sistemas, VetorLeads, VetorOS, VetorPet e Desgarrados.
3. Identificar alterações pertencentes a cada implementação.
4. Preservar todos os arquivos locais, incluindo os não rastreados.
5. Verificar as referências de imagem em `docker-compose.override.yml` e documentar divergências sem substituí-las automaticamente.
6. Separar as alterações por projeto, evitando misturar SEO, VetorChat, relatórios e outras funcionalidades.
7. Executar verificações estáticas e testes disponíveis.
8. Preparar um plano de commits por repositório e atualização posterior dos gitlinks do superprojeto.
9. Verificar que os commits dos submódulos estejam disponíveis nos respectivos remotos antes de recomendar a publicação do superprojeto.
10. Preparar checklist para a homologação VETORCHAT-SAAS-06.

## Restrições

- Não utilizar containers Docker locais.
- Não realizar commit, push ou deploy sem autorização.
- Não modificar configurações da VPS.
- Não apagar alterações locais ou arquivos não rastreados.
- Não usar reset destrutivo.
- Não insistir repetidamente em ferramentas de teste que falharem por problemas ambientais.
- Testar em navegador quando possível.

## Entrega

Atualizar `executed.md` com os resultados da auditoria, alterações identificadas, riscos, testes e plano de versionamento.

Informar claramente se o projeto está pronto para homologação operacional.