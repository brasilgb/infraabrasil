# VETORCHAT-SAAS-09 — EXECUÇÃO FINAL, COMMIT, PUSH E DEPLOY

## MISSÃO

Encerrar o ciclo de desenvolvimento do VetorChat e publicar as implementações concluídas.

**Esta instrução autoriza commit, push, build, deploy e migrations necessárias, mediante as verificações de segurança abaixo.**

Não transformar a execução em outra auditoria interminável.

Já foram realizadas oito etapas de implementação, correções, testes, revisões e organização de código.

Utilizar o trabalho anterior, especialmente:

- `executed.md`
- `docs/vetorchat-saas-08/staging-manifest.md`
- Histórico VETORCHAT-SAAS-01 a 08.

## 1. Prioridade absoluta: ENTREGAR

Executar nesta ordem:

1. Resolver o ponto crítico de middleware em `gateway/vetorleads/routes/settings.php`.
2. Separar o staging conforme o manifesto existente.
3. Executar uma rodada final de testes relevantes.
4. Corrigir somente falhas reais introduzidas pelas implementações.
5. Criar commits separados por funcionalidade.
6. Realizar push dos submódulos.
7. Atualizar e publicar os gitlinks do superprojeto.
8. Gerar ou atualizar as imagens necessárias na VPS.
9. Executar deploy controlado.
10. Confirmar funcionamento HTTP e disponibilidade dos serviços.
11. Registrar o resultado em `executed.md`.

Não reiniciar auditorias já concluídas sem evidências novas.

## 2. Testes — limite objetivo

A etapa 08 já confirmou:

- ABrasil: 81 testes aprovados.
- VetorLeads: 18 testes aprovados.
- VetorOS: 13 testes aprovados.
- VetorOS: TypeScript aprovado.
- VetorOS: ESLint dos relatórios aprovado.

Reexecutar esses testes uma vez após as correções finais.

Caso falhem, investigar e corrigir a causa.

Limitar a duas tentativas de repetição por problema, exceto quando houver nova causa técnica comprovada.

Não bloquear a publicação por warnings ambientais conhecidos e sem impacto operacional.

Testes VetorPet devem ser executados quando as dependências estiverem disponíveis no ambiente autorizado.

Não instalar containers locais para testar.

Não instalar navegadores nem insistir em testes visuais que não funcionam.

Se o navegador estiver indisponível, usar testes HTTP e de integração disponíveis e registrar a limitação.

**Não confundir ausência de testes de navegador com aprovação visual.**

## 3. Segurança obrigatória

Antes do push:

- Confirmar isolamento entre tenants.
- Confirmar middleware e autorizações.
- Não incluir segredos.
- Não incluir `.env` privado.
- Não incluir logs ou caches.
- Não incluir alterações fora do escopo.
- Confirmar ausência de erros críticos conhecidos.

Não afrouxar autenticação, autorização ou validação de origem para facilitar testes.

Se houver falha crítica de segurança, bloquear somente o componente afetado e informar claramente.

## 4. Git e publicação

Criar commits organizados para:

- ABrasil SEO.
- ABrasil VetorChat.
- VetorLeads VetorChat.
- VetorLeads branding/manual.
- VetorOS relatórios.
- VetorOS VetorChat.
- VetorPet VetorChat, após validação.

Utilizar staging seletivo conforme o manifesto.

Não usar `git add .` ou `git add -A`.

Publicar primeiro os commits dos submódulos e confirmar que os hashes estão disponíveis nos respectivos remotos.

Somente depois atualizar e publicar os gitlinks do superprojeto.

Se o remoto privado VetorLeads continuar exigindo autenticação, registrar o impedimento e não tentar contorná-lo com credenciais inseguras.

## 5. Deploy na VPS

Infraestrutura oficial:

`/opt/infra-abrasil`

Não criar containers no ambiente local de desenvolvimento.

Na VPS autorizada:

- Inspecionar o Compose existente.
- Confirmar serviços, volumes, redes e dependências.
- Confirmar as tags reais das imagens.
- Preservar bancos de dados e volumes.
- Verificar backups antes de alterações em produção.
- Gerar as imagens atualizadas com os commits publicados.
- Executar migrations necessárias e compatíveis, após revisão e backup.
- Atualizar somente os serviços afetados.
- Não utilizar `docker compose down -v`.
- Não apagar volumes nem bancos.
- Não executar comandos destrutivos.

O `pull_policy: never` não deve impedir o uso de imagens novas: verificar a estratégia real de build e aplicar tags compatíveis com o Compose.

Preservar o restante da infraestrutura.

## 6. Homologação operacional

Verificar em produção:

1. Site ABrasil carregando.
2. Sitemap e robots respondendo.
3. Widget VetorChat sendo apresentado.
4. VetorLeads acessível.
5. APIs do widget respondendo.
6. Inbox carregando.
7. VetorOS funcionando.
8. VetorPet funcionando, se publicado.
9. WAHA conectado.
10. Identificação correta de tenant e produto.

Utilizar verificações HTTP e diagnósticos seguros.

Mensagens ponta a ponta somente com identidade de teste autorizada, sem contatar clientes reais.

Não habilitar automações para contatos reais sem validação específica.

Se não houver identidade de teste disponível, registrar a pendência operacional sem inventar homologação.

## 7. Critério de conclusão

Classificar o resultado como:

**PUBLICADO E HOMOLOGADO:** deploy concluído e fluxos essenciais testados efetivamente.

**PUBLICADO COM PENDÊNCIAS:** deploy concluído, serviços saudáveis, mas alguma homologação não crítica permanece pendente; não liberar a funcionalidade afetada para clientes até validá-la.

**PUBLICAÇÃO PARCIAL:** somente alguns produtos puderam ser publicados com segurança.

**BLOQUEADO:** impedimento crítico de segurança, dependência, autenticação ou infraestrutura.

Não declarar sucesso sem evidências.

## 8. Relatório final

Atualizar `executed.md` com:

- Commits e hashes.
- Push realizado.
- Imagens geradas.
- Serviços atualizados.
- Migrations realizadas, se houver.
- Resultado dos testes.
- Estado HTTP.
- Estado WAHA.
- Funcionamento do widget e inbox.
- Pendências realmente impeditivas.
- Resultado final da publicação.

Não iniciar nova etapa de planejamento se a implementação puder ser concluída nesta execução.

**OBJETIVO FINAL: ENTREGAR O VETORCHAT FUNCIONANDO NA VPS, COM SEGURANÇA, SEM NOVAS RODADAS DES