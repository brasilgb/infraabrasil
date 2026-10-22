# VETORCHAT-SAAS-07 — Saneamento final, auditoria e preparação de publicação

## Objetivo

Concluir os bloqueios técnicos documentados em VETORCHAT-SAAS-06, organizar as alterações locais e preparar os projetos para commits e homologação controlada.

Não realizar deploy, push, commit ou alterações de infraestrutura nesta etapa.

Ler os relatórios anteriores, comparar o `correio.md` e preservar o histórico em `docs/vetorchat-saas-07/`.

## 1. VetorOS — Eliminar os 110 erros de ESLint

Esta é a principal prioridade.

Inspecionar o `ReportController`, seus recursos, respostas e consumidores frontend.

Mapear os payloads reais e construir contratos TypeScript específicos para os componentes existentes.

Tipar:

- Relatórios financeiros, caixa e faturamento.
- Relatórios operacionais e estatísticos.
- Dados de empresas, filiais e usuários.
- Propriedades dos componentes PDF.
- Filtros, callbacks e parâmetros.
- Estados compartilhados do Inertia.
- Estruturas de tabelas e agregações.

Eliminar os 110 usos de `any` sem modificar regras de negócio, totais financeiros, cálculos, filtros ou apresentação.

Não usar `eslint-disable`, `@ts-ignore`, `as any` ou `unknown` indiscriminado para mascarar problemas.

Preferir interfaces reutilizáveis somente quando os contratos realmente forem equivalentes.

Após cada grupo lógico de alterações, executar TypeScript e lint direcionado.

Se houver erros preexistentes fora do escopo, identificá-los separadamente.

Critérios de aceite:

- Zero erros ESLint no escopo dos relatórios.
- `npm run types` aprovado.
- Testes aplicáveis aprovados.
- Nenhuma regressão funcional.

## 2. VetorOS — Resolver warnings do Pest

Investigar detalhadamente os warnings de:

- `ReportControllerTest.php`
- `Site/VetorChatInstallationTest.php`

Capturar as mensagens completas e suas origens.

Distinguir:

- Warnings da aplicação.
- Deprecações de dependências.
- Configuração de PHPUnit/Pest.
- Problemas ambientais.
- Avisos de cobertura ou metadados.

Corrigir avisos controláveis pelo projeto sem suprimir globalmente sua emissão.

Não atualizar dependências importantes sem justificativa técnica.

Registrar separadamente warnings externos que não possam ser corrigidos com segurança.

Reexecutar os testes.

## 3. VetorLeads — Consolidar estabilidade

Confirmar novamente as oito rotas do widget e a execução conjunta dos testes.

Verificar o comportamento com cache de rotas regenerado em ambiente de teste controlado, além da execução sem cache.

Garantir que o procedimento de publicação considere a regeneração dos caches Laravel após a implantação do código novo.

Não incluir arquivos gerados de cache nos commits.

Revalidar:

- Resolução de tenant.
- Identificação de produto.
- Origem/domínio autorizado.
- Token do visitante.
- Inbox.
- Automatizações.
- Separação de dados multitenant.

Não modificar rotas que já estão corretas.

## 4. ABrasil Sistemas — Verificação final

Confirmar a integridade de:

- SEO dinâmico.
- `robots.txt`.
- Sitemap.
- Consentimento de cookies.
- GA4.
- Widget VetorChat.
- Identificação do produto ABrasil.

Preservar os 81 testes anteriormente aprovados.

Não introduzir mudanças visuais ou novas funcionalidades.

## 5. VetorPet — Validação

Verificar se existe ambiente previamente preparado com dependências disponíveis.

Caso exista, executar testes.

Caso não exista, realizar auditoria estática dos arquivos alterados e registrar a pendência.

Não instalar dependências automaticamente.

Não bloquear as validações independentes dos demais projetos pela indisponibilidade do ambiente VetorPet, mas manter a homologação completa condicionada à validação necessária.

## 6. Auditoria Git obrigatória

Auditar individualmente todos os repositórios e submódulos.

Classificar cada arquivo modificado ou não rastreado conforme sua funcionalidade:

- ABrasil SEO.
- ABrasil VetorChat.
- VetorLeads VetorChat.
- VetorLeads branding/manual.
- VetorOS relatórios.
- VetorOS VetorChat.
- VetorPet VetorChat.
- Infraestrutura compartilhada.
- Alterações preexistentes ou sem classificação.

Verificar arquivos sensíveis, segredos, `.env`, logs, caches e artefatos gerados.

Não adicionar arquivos indiscriminadamente com `git add .` ou `git add -A`.

Propor uma sequência de commits coesa, indicando exatamente quais arquivos pertencem a cada commit.

Inspecionar o estado dos remotos e dos gitlinks sem realizar push.

Não descartar alterações locais.

## 7. Compatibilidade de infraestrutura

Auditar, em modo somente leitura, as referências de imagens e tags existentes.

Examinar `docker-compose.override.yml` sem alterá-lo.

Verificar se as tags declaradas correspondem às versões de código pretendidas.

Documentar qualquer incompatibilidade ou dependência de imagem ainda não publicada.

Não executar Docker ou Compose localmente.

Não substituir tags ou alterar `pull_policy`.

## 8. Política permanente de execução

Não criar containers locais.

Não instalar navegador para satisfazer testes visuais.

Se houver navegador previamente funcional, utilizá-lo para inspeções aplicáveis.

Se não houver, registrar a pendência e executar os testes alternativos adequados.

Não insistir em testes de navegador que falhem por limitações ambientais.

Não executar testes indefinidamente: investigar a causa e repetir somente após mudança ou nova evidência.

Priorizar Pest, PHPUnit, TypeScript, ESLint, testes HTTP sintéticos e build aplicável.

Não utilizar contatos reais, sessões reais de clientes nem enviar mensagens WhatsApp nesta etapa.

Não realizar:

- Commit.
- Push.
- Deploy.
- Migration de produção.
- Modificação da VPS.
- Inicialização ou alteração de containers.
- Alterações destrutivas em banco de dados.
- Mudanças não relacionadas ao escopo.

## 9. Critérios de conclusão

A execução poderá ser considerada tecnicamente concluída quando:

1. VetorOS tiver lint e TypeScript aprovados no escopo.
2. Os warnings Pest tiverem sido resolvidos ou classificados com evidência.
3. VetorLeads mantiver os 18 testes aprovados.
4. ABrasil mantiver os testes aprovados.
5. VetorPet tiver sua situação de validação claramente documentada.
6. Todas as alterações Git estiverem classificadas.
7. A sequência de commits estiver pronta para autorização.
8. Nenhuma alteração de infraestrutura ou produção tiver sido realizada.

Não declarar homologação operacional completa sem validação visual e integração real autorizada com WAHA.

## 10. Entrega

Atualizar `executed.md` contendo:

- Resumo executivo.
- Causas e correções.
- Contagem de erros antes/depois.
- Resultado de cada suíte.
- Warnings remanescentes.
- Situação individual dos quatro projetos.
- Relação precisa de arquivos por commit proposto.
- Estado dos remotos e gitlinks.
- Pendências de imagens e infraestrutura.
- Bloqueios operacionais.
- Recomendação para a etapa 08.

**Resultado esperado: código estabilizado e pacote de commits preparado para revisão, sem publicação.**