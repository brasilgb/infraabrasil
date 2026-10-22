# Manifesto de staging — VETORCHAT-SAAS-08

Inventário produzido em 22/10/2026 por `git status --porcelain=v1 --untracked-files=all` nos quatro submódulos e no superprojeto. Nenhum arquivo foi staged por esta auditoria.

Cada linha registra estado, funcionalidade, commit proposto e necessidade de inclusão. Assets `public/build/**` são excluídos porque os Dockerfiles executam `npm ci`/build e os entrypoints copiam a saída para o volume persistente. Arquivos mistos exigem staging por hunk.

## Contagem por grupo

- A — SEO: 104 arquivo(s)
- A — SEO / B — VetorChat (staging seletivo): 2 arquivo(s)
- B — VetorChat ABrasil: 2 arquivo(s)
- C — VetorChat VetorLeads: 38 arquivo(s)
- C — VetorChat / D — Branding e manual (staging seletivo): 1 arquivo(s)
- D — Branding e manual VetorLeads: 20 arquivo(s)
- E — Relatórios VetorOS: 44 arquivo(s)
- F — VetorChat VetorOS: 4 arquivo(s)
- G — VetorChat VetorPet: 4 arquivo(s)
- H — Superprojeto, documentação e gitlinks: 42 arquivo(s)
- I — Infraestrutura, alteração independente: 1 arquivo(s)
- Não incluir: 267 arquivo(s)
- Pendentes de classificação manual: 0

## Riscos e dependências

- ABrasil: `.env.example` e `resources/views/app.blade.php` têm hunks SEO e VetorChat; separar por hunk.
- VetorLeads: `resources/views/app.blade.php` mistura VetorChat e branding; `routes/settings.php` altera middleware/grupos preexistentes e requer revisão linha a linha.
- Commit H só deve ocorrer após commits internos e atualizar gitlinks. Os gitlinks atuais ainda apontam aos HEADs locais registrados em `executed.md`.
- Commit I é independente; as tags em `docker-compose.override.yml` não foram verificadas em registry/runtime.
- Estados staged anteriores foram preservados: a remoção de `gateway/abrasilsistemas/public/robots.txt` já estava staged.

## Dependências, validações e riscos por commit

| Grupo | Dependências e validações | Riscos/limites |
|---|---|---|
| A — SEO ABrasil | Sem dependência de VetorChat; requer staging seletivo em `.env.example` e `resources/views/app.blade.php`; 81 testes direcionados aprovados; remoção robots coberta por rota dinâmica. | OAuth/credenciais reais e indexação externa não foram testados; token GSC criptografado pelo cast `encrypted`. |
| B — VetorChat ABrasil | Depende do contrato público de widget do VetorLeads; 81 testes direcionados do conjunto aprovados; separar hunks chat dos SEO. | Sem envio real, WAHA ou validação externa de domínio em runtime. |
| C — VetorChat VetorLeads | Depende de tenant/produto/widget configurados; oito rotas e 18 testes/216 assertions aprovados com e sem cache. | `routes/settings.php` exige revisão linha a linha dos middlewares e autorizações; remoto não acessível sem autenticação. |
| D — Branding/manual VetorLeads | Independente do backend para identidade e manual; separar hunk de `resources/views/app.blade.php`. | Não incluir arquivos compilados de `public/build`; confirmar manual contra telas finais antes de publicar. |
| E — Relatórios VetorOS | TypeScript e lint direcionado aprovados; 13 testes/46 assertions aprovados sem warnings usando config de teste temporária. | Relatórios não foram exercitados visualmente em browser/runtime; sem mudança intencional de cálculos. |
| F — VetorChat VetorOS | Depende do serviço VetorLeads, tenant institucional, produto VetorOS, domínio/chave configurados. | Sem WAHA ou validação de domínio/runtime; conferir placeholders de ambiente no deploy. |
| G — VetorChat VetorPet | Depende do serviço VetorLeads, tenant institucional e produto VetorPet. | Ambiente de testes indisponível (`vendor`/`node_modules` ausentes); nenhuma instalação foi feita. |
| H — Superprojeto/gitlinks | Depende dos commits A–G revisados; atualizar cada gitlink somente para o commit interno escolhido. | Gitlink atual aponta para HEAD local existente; arquivos históricos/logs permanecem fora do pacote de código. |
| I — Infraestrutura | Independente dos commits de aplicação apenas após confirmar tags publicadas e fluxo de deploy. | Tags não verificadas, `pull_policy: never`; nenhuma operação Docker nem validação em `/opt/infra-abrasil` foi feita. |

## Arquivos por commit proposto

| Commit/grupo | Repositório | Caminho | Estado | Funcionalidade | Necessidade de inclusão |
|---|---|---|---|---|---|
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Console/Commands/BlogSeoDraftsCommand.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Console/Commands/SeoAuditCommand.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Console/Commands/SeoCheckCommand.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/Admin/BlogPostController.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/Admin/Seo/AnalyzeController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/Admin/Seo/AuditController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/Admin/Seo/IndexingController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/Admin/Seo/IntegrationController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/Admin/Seo/KeywordController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/Admin/Seo/OverviewController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/Admin/Seo/PageController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/Admin/Seo/RedirectController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/Admin/Seo/SettingsController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/Admin/Seo/StructuredDataController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/ContactController.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/IndexNowKeyController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/PublicBlogController.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/RobotsController.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/SiteController.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Controllers/SitemapController.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Middleware/ApplySeoRedirects.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Http/Middleware/HandleInertiaRequests.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Jobs/RunSeoAudit.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Models/Lead.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Models/SeoAudit.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Models/SeoAuditIssue.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Models/SeoAuditPage.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Models/SeoChangeLog.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Models/SeoMeta.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Models/SeoRedirect.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Models/Setting.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Providers/AppServiceProvider.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/ContentAnalyzer.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/GoogleIntegration.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/HtmlInspector.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/IndexNow.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/PageRegistry.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/RedirectManager.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/RobotsBuilder.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/SeoAuditor.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/SeoChangeLogger.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/SeoMetaStore.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/SeoSettings.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/SitemapBuilder.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Services/Seo/StructuredData.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Support/PublicPages.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/app/Support/Seo.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/bootstrap/app.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/composer.lock` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/config/seo.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/database/data/blog-seo-drafts.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/database/migrations/2026_10_21_120000_create_seo_tables.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/database/migrations/2026_10_21_140000_add_seo_blog_category.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/docs/imagens-do-site.md` |  M working tree | documentação SEO/site | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/docs/seo-deploy-e-search-console.md` | não rastreado | documentação SEO/site | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/public/images/site/heroes/seo-1280.avif` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/public/images/site/heroes/seo-1280.webp` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/public/images/site/heroes/seo-1920.avif` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/public/images/site/heroes/seo-1920.webp` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/public/images/site/heroes/seo-640.avif` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/public/images/site/heroes/seo-640.webp` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/public/images/site/heroes/seo-og.jpg` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/public/robots.txt` | D  staged | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/components/cookie-consent.tsx` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/components/nav-main.tsx` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/components/seo/editorial-analysis.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/components/seo/indicators.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/components/seo/meta-fields.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/components/seo/seo-page.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/components/seo/serp-preview.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/components/site/seo-head.tsx` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/components/site/seo-included.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/components/site/visuals.tsx` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/data/hero-images.ts` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/data/site.ts` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/lib/admin-navigation.ts` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/lib/analytics.ts` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/lib/seo.ts` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/blog/form.tsx` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/audits/index.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/audits/show.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/indexing.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/integrations.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/keywords.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/overview.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/pages/edit.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/pages/index.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/redirects/form.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/redirects/index.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/settings.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/admin/seo/structured-data.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/contato.tsx` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/errors/404.tsx` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/legal/cookies.tsx` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/servicos.tsx` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/site/seo.tsx` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/pages/site/servico.tsx` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/js/types/navigation.ts` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/resources/views/sitemap-index.blade.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/routes/console.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/routes/web.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/tests/Feature/PublicSiteTest.php` |  M working tree | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/tests/Feature/SeoServiceTest.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO | ABrasil Sistemas | `gateway/abrasilsistemas/tests/Feature/SeoTest.php` | não rastreado | SEO institucional, sitemap, robots, site público ou analytics | incluir |
| A — SEO / B — VetorChat (staging seletivo) | ABrasil Sistemas | `gateway/abrasilsistemas/.env.example` |  M working tree | arquivo com hunks de duas entregas | incluir hunks A e B em commits separados |
| A — SEO / B — VetorChat (staging seletivo) | ABrasil Sistemas | `gateway/abrasilsistemas/resources/views/app.blade.php` |  M working tree | arquivo com hunks de duas entregas | incluir hunks A e B em commits separados |
| B — VetorChat ABrasil | ABrasil Sistemas | `gateway/abrasilsistemas/config/services.php` |  M working tree | configuração/instalação/teste do widget | incluir |
| B — VetorChat ABrasil | ABrasil Sistemas | `gateway/abrasilsistemas/tests/Feature/InstitutionalChatWidgetTest.php` | não rastreado | configuração/instalação/teste do widget | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/.env.example` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Controllers/Api/PublicChatWidgetController.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Controllers/Api/TenantWhatsappEventController.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Controllers/Api/WebChatController.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Controllers/ChatWhatsappBridgeController.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Controllers/ReceivedContactsController.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Controllers/Settings/ChatProductController.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Controllers/Settings/ChatWidgetController.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Controllers/SupportAutomationController.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Controllers/SupportInboxController.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Middleware/EnsureChatAccess.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Middleware/ResolvePublicChatWidget.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Middleware/ResolveWidgetCors.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Models/ChatProduct.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Models/TenantChatWidget.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Models/WebConversation.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Services/SupportAutomation.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Services/SupportAutomationDelivery.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Services/SupportWhatsappConversation.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Services/TenantChatWidgets.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/app/Services/WidgetInstallationVerifier.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/bootstrap/app.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/config/services.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/database/migrations/2026_10_21_140000_create_tenant_chat_widgets.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/database/migrations/2026_10_21_150000_create_chat_products.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/public/widget.js` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/pages/settings/chat-products.tsx` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/pages/settings/chat-widget.tsx` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/pages/support-automation/index.tsx` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/pages/support-inbox/contacts.tsx` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/pages/support-inbox/index.tsx` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/routes/api.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/routes/settings.php` |  M working tree | configuração de produtos/widget e limites de acesso | incluir; revisar grupos e middleware das rotas movidas |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/routes/web.php` |  M working tree | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/tests/Feature/ChatProductsTest.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/tests/Feature/ChatProductTest.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/tests/Feature/TenantChatWidgetTest.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat VetorLeads | VetorLeads | `gateway/vetorleads/tests/Feature/VetorChatInstallationTest.php` | não rastreado | backend, integração, widget, inbox, automatização ou teste | incluir |
| C — VetorChat / D — Branding e manual (staging seletivo) | VetorLeads | `gateway/vetorleads/resources/views/app.blade.php` |  M working tree | widget do site e favicon/manifest na mesma view | incluir hunks C e D em commits separados |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/app/Http/Controllers/HelpManualController.php` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/public/favicon.ico` | não rastreado | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/public/images/favicon/android-chrome-192x192.png` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/public/images/favicon/android-chrome-512x512.png` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/public/images/favicon/apple-touch-icon.png` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/public/images/favicon/favicon-16x16.png` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/public/images/favicon/favicon-32x32.png` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/public/images/favicon/favicon.ico` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/public/images/vetorleads-logo.webp` | não rastreado | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/public/site.webmanifest` | não rastreado | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/components/app-logo-icon.tsx` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/components/app-logo.tsx` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/components/app-sidebar.tsx` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/components/public-brand.tsx` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/components/vetorleads-public.tsx` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/data/manual.ts` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/layouts/auth/auth-simple-layout.tsx` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/layouts/settings/layout.tsx` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/resources/js/pages/blog/show.tsx` |  M working tree | identidade visual, manual e material público | incluir |
| D — Branding e manual VetorLeads | VetorLeads | `gateway/vetorleads/scripts/generate-brand-assets.php` | não rastreado | identidade visual, manual e material público | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/app/Http/Controllers/App/ReportController.php` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/components/reports/report-preview-button.tsx` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/lib/reports/open-report-preview.ts` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/lib/reports/order-report-summary.ts` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/lib/reports/report-labels.ts` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/lib/reports/report-types.ts` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/cashier-report.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/customers-report.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/expenses-report.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/fiscal-report.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/index.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/order-statistics.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/orders-daily.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/orders-report.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/parts-report.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/CashierDailyReportPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/CashierPeriodReportPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/CustomerReportPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/ExpenseReportPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/FiscalReportPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/OrderReportPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/OrderStatisticsPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/PartReportPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/QualityReportPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/SaleReportPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/SchedulesReportPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/pdf/TechnicianProductivityPDF.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/quality-report.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/sales-report.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/schedules-report.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/technician-productivity.tsx` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/view.tsx` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/views/customers-report-view.tsx` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/views/expenses-report-view.tsx` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/views/fiscal-report-view.tsx` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/views/orders-report-view.tsx` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/views/parts-report-view.tsx` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/views/report-table.tsx` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/views/sales-report-view.tsx` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/views/schedules-report-view.tsx` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/pages/app/reports/views/summary-card.tsx` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/resources/js/types/report-types.d.ts` | não rastreado | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/routes/app.php` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| E — Relatórios VetorOS | VetorOS | `gateway/vetoros/tests/Feature/App/ReportControllerTest.php` |  M working tree | contratos, telas, PDFs, endpoints ou testes de relatórios | incluir |
| F — VetorChat VetorOS | VetorOS | `gateway/vetoros/.env.example` |  M working tree | configuração, instalação e teste do widget | incluir |
| F — VetorChat VetorOS | VetorOS | `gateway/vetoros/config/services.php` |  M working tree | configuração, instalação e teste do widget | incluir |
| F — VetorChat VetorOS | VetorOS | `gateway/vetoros/resources/views/app.blade.php` |  M working tree | configuração, instalação e teste do widget | incluir |
| F — VetorChat VetorOS | VetorOS | `gateway/vetoros/tests/Feature/Site/VetorChatInstallationTest.php` | não rastreado | configuração, instalação e teste do widget | incluir |
| G — VetorChat VetorPet | VetorPet | `gateway/vetorpet/.env.example` |  M working tree | configuração, instalação e teste do widget | incluir |
| G — VetorChat VetorPet | VetorPet | `gateway/vetorpet/config/services.php` |  M working tree | configuração, instalação e teste do widget | incluir |
| G — VetorChat VetorPet | VetorPet | `gateway/vetorpet/resources/views/app.blade.php` |  M working tree | configuração, instalação e teste do widget | incluir |
| G — VetorChat VetorPet | VetorPet | `gateway/vetorpet/tests/Feature/VetorChatInstallationTest.php` | não rastreado | configuração, instalação e teste do widget | incluir |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `correio.md` |  M working tree | documentação da etapa 08 | incluir |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-02/correio.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-02/previous-executed.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-03/correio-comparison.diff` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-03/correio.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-03/instruction-hashes.txt` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-03/previous-executed.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-03/quality-checks.txt` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-03/self-evaluation.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-04/correio-comparison.diff` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-04/correio.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-04/executed-anterior.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-05/correio-comparison.diff` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-05/correio.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-05/executed-anterior.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-05/instruction-hashes.txt` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-051/checksums.txt` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-051/correio.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-051/executed-anterior.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-051/vetorpet-local-changes.patch` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-051/vetorpet-unmerged-index.txt` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-053/checksums.txt` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-053/correio-comparison.diff` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-053/correio.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-053/executed-anterior.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-054/checksums.txt` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-054/correio.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-054/executed.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-06/checksums.txt` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-06/correio.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-06/executed.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-07/checksums.txt` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-07/correio.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-07/executed.md` | não rastreado | arquivo textual de histórico/documentação | propor inclusão como arquivo histórico |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-08/correio.md` | não rastreado | documentação da etapa 08 | incluir |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-08/executed-anterior.md` | não rastreado | documentação da etapa 08 | incluir |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `docs/vetorchat-saas-08/instruction-hashes.txt` | não rastreado | documentação da etapa 08 | incluir |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `executed.md` |  M working tree | documentação da etapa 08 | incluir |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `gateway/abrasilsistemas` |  M working tree | gitlink do submódulo; update só após commit interno | incluir após commits internos |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `gateway/vetorleads` |  M working tree | gitlink do submódulo; update só após commit interno | incluir após commits internos |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `gateway/vetoros` |  M working tree | gitlink do submódulo; update só após commit interno | incluir após commits internos |
| H — Superprojeto, documentação e gitlinks | Superprojeto | `gateway/vetorpet` |  M working tree | gitlink do submódulo; update só após commit interno | incluir após commits internos |
| I — Infraestrutura, alteração independente | Superprojeto | `docker-compose.override.yml` |  M working tree | tags de imagens e pull_policy | incluir em proposta separada, após validar imagens |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/correio.md` |  M working tree | instrução/relatório histórico do subprojeto | preservar local; fora do release |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/executed.md` |  M working tree | instrução/relatório histórico do subprojeto | preservar local; fora do release |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/404-CqtMX4qn.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/404-DD0yT3HP.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/admin-navigation-CGFQiSd2.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/app-BrcJHbHY.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/app-CCspXhCp.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/app-CHS8RV4H.css` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/app-FJisnWYI.css` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/app-pagination-DYrSXGby.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/appearance-BNoupiwy.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/appearance-Cm6CWpgX.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/appearance-CR8m-iRr.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/appearance-VrwMZQdQ.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/arrow-left-CrEeZ9Ox.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/arrow-left-r5HXfwfZ.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/atendimento-Bi8v3d4V.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/atendimento-LhIHC0Ko.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/audits-DoIcTqZ8.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/badge-B6pH254P.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/badge-b7Ys1Olg.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/blog-CLtWvQuS.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/blog-DZySFl7R.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/blog-opikV9oY.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/blog-public-layout-4VO2ukAG.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/blog-public-layout-MCBDiamW.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/blog-w0rrqY1A.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/book-open-BXv0Fa6h.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/book-open-CoB50gAT.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/button-Ck26vt7X.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/button-CVefv-wb.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/card-BFuCSp0G.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/card-C0zPVUf3.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/categories-BDZpRGZ0.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/categories-Dtf-hBkE.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/check-CkNbQ1Wb.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/check-WnkepWdT.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/checkbox-_QCnzCw2.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/checkbox-D0Dava_3.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/chevron-right-CileVDoL.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/circle-alert-CPngzYRY.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/circle-check-BKxxRC8l.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/circle-play-OkQFn_hy.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/collapsible-DVIq63YK.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/comments-CH3lcyEd.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/comments-DkDzz6cz.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/company-whatsapp-connection-Dg2xFhHb.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/company-whatsapp-connection-ViEiRPQD.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/confirm-password-5LCShY4I.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/confirm-password-DARMJjYD.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/contato-BRFi3j5h.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/contato-DrkYa91n.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/cookies-B0hVEURJ.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/cookies-dYIr01IR.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/create-djulJccs.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/create-gI4KBsPn.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/createLucideIcon-BLLijEhV.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/createLucideIcon-DnH62YIl.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dialog-C-E9Tby9.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dialog-X7PlaFXa.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-BARj8GKc.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-Be7G1iS-.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-BS-9hFOC.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-BVvA0dAa.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-C9u4FolQ.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-CqSIfFII.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-CT96gyCr.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-CuLjAVep.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-DckhUnva2.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-DVQpVDP9.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-L2e613vv.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dist-vnNPP6EW.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dropdown-menu-Bh38afvk.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/dropdown-menu-Dl2Jx1oF.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/edit-CeF5byw8.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/edit-CF1sWWio.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/edit-MWd8imzq.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/es2015-ClxoMR0s.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/es2015-DTnJifxG.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/external-link-CMQeflAC.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/external-link-CVBNZ9vg.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/eye-8wBic_C-.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/eye-JaVgz5M-.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/forgot-password-BB6Lyxq6.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/forgot-password-wqgunp-M.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/form--v1dzvnx.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/form-BbiLdktQ.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/form-BE--mJns.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/form-CBx-1toq.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/form-cCSTLkI3.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/form-D8jxNqoV.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/form-f-7iX2Ek.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/globe-CfeUlL6c.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/globe-DUX64g9M.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/heading-B7FHQkKi.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/heading-borZhMU1.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/inbox-B5T9euAm.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/inbox-D-_6DNjH.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/indexing-wABg87GF.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/indicators-cAyrToor.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/input-Cba6AIxh.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/input-error-CJJH4Pjn.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/input-error-DK2C_BUb.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/input-TYPUM9vN.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/integrations-BS28AAgu.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/keywords-CpNNcZQK.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/label-kD4_s41o.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/label-mLmMLff6.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/loader-circle-CmlMIJqU.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/loader-circle-Eo-qmRO9.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/login-B2FigWou.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/login-M-kNpgf5.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/mail-BGDA3rEL.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/mail-Cx_9FFRV.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/meta-fields-DxqTFr8B.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/overview-C0hvNUPy.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/pages-BzQ0K9tQ.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/password-input-CCIKlPNw.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/password-input-kknDpH2t.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/pencil-CID_Xg6e.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/plus-BXygOGLM.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/plus-C_RO7cSG.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/portfolio-B3LpIIzC.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/portfolio-BG2rOfPW.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/privacidade-BdYUhNSj.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/privacidade-BqcKeo4U.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/profile-DHoBPf_3.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/profile-Tlc2fjw8.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/quote-B8rVic7G.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/quote-Bzd6wRh0.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/redirects-BEF1nfH0.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/register-DuVPe_1N.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/register-joWU6_41.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/reset-password-C32ytlbu.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/reset-password-C3ERK3PR.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/routes-BdZuJqae.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/routes-uSd5xabo.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/search-CDlXMkIA.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/search-DG9k2p3m.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/security-9M38J3iG.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/security-BDxH5AYS.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/seo-B6VmQLz4.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/seo-BvM1DmcN.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/seo-head-CEckWSjn.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/seo-head-CHNYO1O5.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/seo-included-C1RU5nor.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/seo-page-E3XAjrMA.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/servico-BG9Lbhm-.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/servico-JagTiMuL.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/servicos-Bt6qpN3k.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/servicos-BTTklPWM.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/settings-zPuMoSz5.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/show-CdTCd0sp.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/show-CTTXAjk5.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/show-CXBJdJpg.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/show-Dra2RbQy.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/show-DRROstxk.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/sistema-Bx0Z6IX9.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/sistema-Co4ZDMQP.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/sistemas-D80HqbU5.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/sistemas-Jzu-AXHh.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/site-layout-iRrVSQ1s.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/site-layout-lzaJdMb9.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/sobre-BHshBEmq.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/sobre-RH_FzSYF.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/solicitacoes-BCAIcawr.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/solicitacoes-CfZc3BE4.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/solicitacoes-DFfDkepq.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/solicitacoes-uOOb-W7G.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/spinner-CJ_jyt-D.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/spinner-CTQp-ExJ.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/structured-data-Dit3_0lC.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/termos-bVTTZf1x.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/termos-Dw2m8WJ6.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/testimonials-BGOO4goV.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/testimonials-wyTHhO-v.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/text-link-1UQMbbP6.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/text-link-DQmIAl56.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/trash-2-3Yu0D-_w.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/trash-2-D4DyzFlw.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/triangle-alert--x3oH7i5.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/triangle-alert-DS33ryQb.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/upload-CscY7yaB.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/upload-mgmF5L7b.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/user-round-BtejDDkQ.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/user-round-Di5epDuc.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/users-CFlQqsS8.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/users-CKEEzgdi.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/users-CmbFiDtL.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/users-f25pz-M9.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/verify-email-Dx4ZHl1O.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/verify-email-xRmjQn2u.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/visuals-C6piXGaZ.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/visuals-LR49flqW.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/welcome-DfsG9KSk.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/welcome-RKaoD0Mu.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/whatsapp-D_TBOKKP.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/whatsapp-D2E3PIft.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/whatsapp-float-C_f-G00d.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/whatsapp-float-DwnA_GWE.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/whatsapp-settings-BQdoHXU3.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/whatsapp-settings-DzisZ5RK.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/x-C3HN13Qa.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/assets/x-V7rxUYmE.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | ABrasil Sistemas | `gateway/abrasilsistemas/public/build/manifest.json` |  M working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | Superprojeto | `docs/vetorchat-saas-02/browser-results.json` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-02/settings-desktop.png` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-02/settings-mobile.png` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-02/widget-desktop.png` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-02/widget-mobile-b.png` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/abrasil-final-targeted-tests.json` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/abrasil-tests.json` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/browser-check.mjs` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/browser-results.json` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/browser-run.log` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/cookie-consent.png` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/installation-snippet.html` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/settings-desktop.png` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/settings-mobile.png` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/vetorleads-tests.json` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/vite-build.log` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/widget-desktop.png` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/widget-mobile-a.png` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | Superprojeto | `docs/vetorchat-saas-03/widget-mobile-b.png` | não rastreado | captura, resultado ou artefato histórico gerado | não incluir no pacote de código |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/404-C35Jl2K8.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/404-DSmYOL4l.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/app-DKoY4wNT.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/app-layout-BjXAyZDq.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/app-layout-Dx8tvQ8b.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/app-logo-icon-BpNtXlGY.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/app-Sy6uiTIQ.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/blog-C1QcKOut2.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/blog-D6eV5v302.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/blog-public-layout-Blgukcmt.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/blog-public-layout-BV44hD1J.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/company-websites-BGOlYpLG.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/company-websites-CMLDRwEN.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/contato-Ce6oypAu.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/contato-FSZ5PJKb.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/cookies-BxzZIeWT.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/cookies-C7CqOXBk.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/lab-iypPWCtF.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/lab-K7ShsvKh.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/privacidade-BidWFhmT.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/privacidade-y5wCVjqq.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/produtos-CXE6OCqZ.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/produtos-DJLL65mE.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/public-brand-C9SleZeX.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/public-brand-DWhYKIHO.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/secondary-page-header-C0v3MNiK.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/secondary-page-header-RthvtzyH.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/servicos-BUYeHGui.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/servicos-E-Mt4ukB.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/show-CJUof_o6.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/show-DAUwKMUt.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/sobre-D96gxu2i.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/sobre-DWphHF0R.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/termos-9uM9nZiz.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/termos-BUnz6p5o.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/vetorleads-public-B0noWppl.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/vetorleads-public-BTmMjKFu.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/welcome-D_ZLmOMu.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/welcome-pzYVAIUL.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/whatsapp-float-Cs2OJpcp.js` | não rastreado | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/assets/whatsapp-float-Dcm22Rpi.js` |  D working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | VetorLeads | `gateway/vetorleads/public/build/manifest.json` |  M working tree | asset compilado; Dockerfile executa build e entrypoint copia para o volume | não incluir (gerado pelo build) |
| Não incluir | Superprojeto | `docs/vetorchat-saas-051/VetorChatInstallationTest.php` | não rastreado | cópia histórica de teste, já coberta pelo código do submódulo | manter como arquivo histórico; não incluir no código |
