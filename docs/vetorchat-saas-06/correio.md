# VETORCHAT-SAAS-06 — Estabilização técnica e preparação para homologação

## Objetivo

Concluir as pendências identificadas na execução VETORCHAT-SAAS-05.4 e preparar os projetos ABrasil Sistemas, VetorLeads, VetorOS e VetorPet para homologação operacional segura.

A prioridade é estabilizar o que já foi implementado. Não adicionar funcionalidades novas nem modificar a arquitetura sem necessidade comprovada.

Ler `executed.md`, o `correio.md` atual e os registros históricos em `docs/vetorchat-saas-054/` antes de iniciar.

Preservar integralmente o trabalho existente.

## 1. VetorLeads — Corrigir falhas funcionais

Investigar os 404 encontrados em:

- `tests/Feature/ChatProductTest.php`
- `tests/Feature/ChatProductsTest.php`

Verificar:

1. Registro efetivo das rotas na aplicação e no ambiente Pest.
2. Prefixos, métodos HTTP e nomes de rotas.
3. Middleware de autenticação e contexto tenant.
4. Configuração dos testes e carregamento de providers.
5. Respostas 404 por ausência de rota versus recursos inexistentes.
6. Factories e fixtures.
7. Políticas de autorização e isolamento multitenant.
8. A falha adicional relacionada à resposta de automação.

Identificar a causa raiz de cada classe de falha antes de modificar código.

Não alterar os testes simplesmente para fazê-los passar se isso esconder um erro real.

Manter o isolamento de dados entre tenants.

Após as correções, executar os dois arquivos Pest em conjunto.

**Critério de aceite:** todos os 18 testes devem passar, ou deve ser apresentado relatório técnico individualizado das falhas remanescentes, com evidências e justificativas.

## 2. VetorOS — Tipagem TypeScript

Resolver os problemas de tipagem identificados nos relatórios e componentes PDF.

Investigar os contratos reais retornados pelo `ReportController`.

Criar interfaces TypeScript específicas para:

- Dados dos relatórios.
- Registros financeiros e operações de caixa.
- Informações de empresa e filial.
- Props dos componentes PDF.
- Callbacks de formulários.
- Estado compartilhado Inertia.
- Filtros, agrupamentos e resultados.

Substituir `any` por contratos reais, sem usar tipagens genéricas que prejudiquem a compilação.

Não utilizar `@ts-ignore`, `eslint-disable` generalizado ou conversões artificiais para mascarar problemas.

Preservar cálculos financeiros, totalizações, filtros, exportações e apresentações atuais.

Corrigir o `no-unused-vars` em `CashierDailyReportPDF.tsx`, desde que a alteração não modifique o comportamento.

Executar:

- `npm run types`
- ESLint direcionado aos arquivos envolvidos.
- Testes aplicáveis aos relatórios.
- Build, somente se as etapas anteriores permitirem.

Comparar os erros antes e depois da implementação.

**Critério de aceite:** ausência de novos erros, tipagem correta e nenhuma regressão dos relatórios.

## 3. ABrasil Sistemas — Integração institucional

Verificar:

- Consentimento de cookies.
- Persistência da escolha do usuário.
- Disparo do GA4 apenas quando autorizado.
- Funcionamento do `RobotsController`.
- Endpoint dinâmico `/robots.txt`.
- Sitemap e regras de indexação.
- Instalação do widget VetorChat.
- Identificação correta do produto ABrasil.

Garantir que a remoção de `public/robots.txt` não resulte em perda de funcionalidade ou conflito de rota.

Executar testes direcionados, ESLint e verificações HTTP disponíveis.

Não alterar o escopo de SEO já concluído.

## 4. VetorPet — Verificação segura

Verificar os arquivos de instalação do widget VetorChat e os contratos de integração.

Não instalar dependências automaticamente.

Caso exista um ambiente já preparado, executar os testes pertinentes.

Caso contrário, documentar a impossibilidade e identificar exatamente quais verificações permanecem pendentes.

Não afirmar aprovação de testes não executados.

## 5. Contrato universal VetorChat

Verificar a compatibilidade da integração entre os produtos do ecossistema ABrasil.

A arquitetura deve manter:

- VetorLeads como backend multitenant do VetorChat.
- Widget reutilizável e padronizado.
- ABrasil Sistemas como tenant proprietário dos produtos internos.
- Identificação individual do produto de origem.
- Token compartilhado somente no escopo autorizado do tenant ABrasil.
- Tokens próprios para tenants externos.
- Segregação dos atendimentos por tenant e produto.
- Integração com WAHA mediante sessão corretamente associada ao tenant.
- Entrada de mensagem pelo widget.
- Armazenamento na inbox.
- Resposta humana ou automatizada, quando habilitada.
- Retorno da resposta ao widget e encaminhamento ao WhatsApp quando aplicável.

Não expor tokens privados no frontend, logs, testes ou relatórios.

Não confundir o token de integração entre serviços com a credencial de sessão do visitante.

Não criar novos mecanismos de autenticação sem necessidade.

## 6. Política de testes obrigatória

**Não utilizar containers locais.**

Nenhum Docker, Docker Compose, criação de imagem ou instalação de serviços locais nesta execução.

Priorizar:

1. Testes unitários.
2. Testes de integração com dados sintéticos.
3. Pest/PHPUnit.
4. TypeScript.
5. ESLint.
6. Build.
7. Verificações HTTP em ambiente já disponível.

Testes de navegador somente quando houver navegador e aplicação funcional previamente disponíveis.

Se o navegador apresentar problemas de inicialização ou incompatibilidade de ambiente, registrar a limitação e prosseguir com os demais testes.

Não instalar navegadores repetidamente.

Não insistir indefinidamente em testes que falham por problemas do ambiente.

Limitar tentativas repetidas àquelas justificadas por uma alteração concreta ou nova evidência.

Não modificar código funcional para contornar uma limitação exclusiva do ambiente de testes.

## 7. Segurança e preservação

É proibido nesta execução:

- Criar ou modificar containers locais.
- Realizar deploy.
- Executar migrations em produção.
- Modificar a VPS.
- Enviar mensagens reais via WhatsApp.
- Executar cobranças reais.
- Utilizar contatos comerciais reais para homologação.
- Apagar trabalho anterior.
- Fazer reset destrutivo do Git.
- Executar commit ou push sem autorização posterior.

Preservar alterações locais preexistentes e os trabalhos paralelos dos projetos.

## 8. Auditoria Git

Inspecionar separadamente:

- ABrasil Sistemas.
- VetorLeads.
- VetorOS.
- VetorPet.
- Superprojeto.
- Infraestrutura compartilhada.

Identificar:

- Arquivos modificados.
- Arquivos staged.
- Arquivos untracked.
- Alterações preexistentes.
- Alterações realizadas nesta execução.
- Dependências entre commits.
- Referências dos submódulos.
- Imagens necessárias e tags declaradas.

Preparar uma proposta de commits separados por funcionalidade.

Não executar os commits.

## 9. Homologação funcional

Preparar testes sintéticos para o fluxo:

`Widget → VetorLeads → Inbox → Atendimento → Resposta → Widget`

Também verificar, por simulação ou integração isolada disponível:

`Widget → VetorLeads → WAHA → WhatsApp`

Confirmar isolamento por tenant, identificação do produto e tratamento de falhas.

Não declarar homologação operacional completa quando depender de integração real não executada.

## 10. Relatório final

Atualizar `executed.md` com:

1. Comparação do `correio.md` anterior e atual.
2. Causa raiz dos 404 no VetorLeads.
3. Correções efetivamente implementadas.
4. Resultado individual dos testes.
5. Quantidade de erros TypeScript/ESLint antes e depois.
6. Verificação da integração universal VetorChat.
7. Situação do VetorPet.
8. Situação de Git, submódulos e imagens.
9. Impedimentos remanescentes.
10. Plano preciso dos commits.
11. Recomendação fundamentada: APTO ou NÃO APTO para homologação.

Caso algum requisito não possa ser concluído, registrar a pendência sem comprometer os demais trabalhos.

**Resultado esperado:** aplicações tecnicamente estabilizadas, testes aplicáveis aprovados e pacote de publicação preparado, porém sem commit, push ou deploy.