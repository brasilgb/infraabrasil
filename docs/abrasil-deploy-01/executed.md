# Execução — ABRASIL-DEPLOY-01

Data: 22/10/2026, ~17:33–17:45 (BRT).

**Estado: ABrasil Sistemas publicado no commit `fc2d8c5` e SEO operacional. A integração com o VetorChat não está ativa**, porque falta configuração: o VetorLeads não tem widget nem produto cadastrado, e a chave do site está vazia (ver Pendências). Nenhuma configuração de produção foi criada ou alterada para isso.

## Verificações iniciais

- **VetorLeads:** operacional em `vetorleads:vetorchat09-853e2c4`, com os três serviços `healthy` e `/up` 200. Ver `docs/vetorchat-saas-09/executed.md`.
- **ABrasil Sistemas:** `gateway/abrasilsistemas` em `fc2d8c5`, igual ao `origin/main` e ao gitlink do superprojeto. Árvore limpa e `git fsck` sem erros.
- **Versão anterior em execução:** imagem de 16/10 13:23, correspondente ao `780bea4`. Entre ela e `fc2d8c5` há 2 commits: o módulo SEO, a página do serviço comercial de SEO, o widget VetorChat e o `composer.lock`.
- **Serviços no Compose:** `abrasilsistema`, `abrasilsistema-worker` e `abrasilsistema-scheduler`. O build é multi-stage (composer → wayfinder → `npm run build` → php-fpm) e o entrypoint copia `public/build` para o volume `abrasilsistema-build`. Os uploads ficam no bind `volumes/abrasilsistema` e não são tocados.
- **Desgarrados:** não foi modificado.

## Backup e rollback

- Dump: `backups/abrasilsistemas_pre_deploy01_20261022_173445.sql`, SHA-256 `ce8b4848aa2c869e0dcf5f65e1595a6b047d46084501de054a78f9158e27fbfa`.
- **Restauração testada** num `mysql:8.4` temporário e isolado (`--network none`): 27/27 tabelas, contagens iguais à produção (users 1, leads 2, blog_posts 1, blog_categories 7, settings 6, migrations 35).
- Imagens anteriores preservadas como `infra-abrasil-abrasilsistema{,-worker,-scheduler}:rollback-780bea4`. As imagens desses serviços não são fixadas no override, e o build sobrescreveria `:latest`.
- **Rollback:** `docker tag infra-abrasil-<svc>:rollback-780bea4 infra-abrasil-<svc>:latest` para os três serviços, depois `docker compose up -d --no-deps abrasilsistema abrasilsistema-worker abrasilsistema-scheduler` e reload do nginx. As migrations são aditivas, então não é preciso reverter o banco.

## Publicação

1. `docker compose build abrasilsistema abrasilsistema-worker abrasilsistema-scheduler`: OK. As dependências do composer e do npm foram instaladas no build, conforme o Dockerfile.
2. Migrations, rodadas com a imagem nova em container avulso (`run --rm --no-deps`):
   - `2026_10_21_120000_create_seo_tables`: DONE (só cria tabelas, protegidas por `hasTable`);
   - `2026_10_21_140000_add_seo_blog_category`: DONE (insere a categoria "SEO e performance" só se não existir).
3. `docker compose up -d --no-deps` dos três serviços: `healthy`. Em seguida `optimize:clear` e `nginx -t` com reload.

MySQL, Redis, WAHA, n8n, VetorOS, VetorPet, VetorLeads e Desgarrados não foram reiniciados.

## Validação de SEO

| Item | Resultado |
|---|---|
| Home, sobre, serviços, sistemas, contato, blog, privacidade, termos | 200 |
| Páginas de serviço (sites, landing pages, SEO técnico) | 200 |
| Redirecionamentos | `/produtos` 301 → `/sistemas`, `/conhecimento` 301 → `/blog`, `/portfolio` 302 → login (área restrita) |
| title / description / canonical | presentes e com canonical absoluto em todas as páginas 200 |
| Open Graph | 8 tags por página; `og:image` responde 200 |
| Dados estruturados | JSON-LD válido: home `Organization` + `WebSite`; SEO `Organization` + `Service` + `BreadcrumbList`. Privacidade e termos sem JSON-LD |
| `robots.txt` | **dinâmico, 200 `text/plain`** (antes do deploy respondia **404**); bloqueia áreas administrativas e aponta para o sitemap |
| `sitemap.xml` | 200, XML válido, 17 URLs, todas com 200, incluindo o serviço de SEO. Abaixo do limite por arquivo sai `urlset` único, como previsto no `SitemapController`; `/sitemaps/1.xml` 200 e `/sitemaps/2.xml` 404 |
| Painel admin SEO (9 telas) | sem login: 302 para `/login`. Com o admin, renderizado no container só com GET: todas 200 |
| Google Search Console | `configured=false`, `connected=false`; callback `https://abrasilsistemas.com.br/admin/seo/integracoes/google/callback` |
| Imagens do serviço de SEO | `seo-{640,1280,1920}.{avif,webp}` e `seo-og.jpg`: 200 com content-type correto |
| Logs após o deploy | sem ERROR/CRITICAL/exception |

## Integração VetorChat

| Item | Resultado |
|---|---|
| `https://vetorleads.com.br/widget.js` | 200 (publicado no VETORCHAT-SAAS-09) |
| Widget no site institucional | **não injetado**. A Blade exige uma `VETORCHAT_WIDGET_KEY` de 48 caracteres, e no container ela está vazia |
| Produto "abrasil" (`data-product="abrasil"`) | **não cadastrado**. `chat_products` vazio no VetorLeads |
| Widget do tenant no VetorLeads | **não existe**. `tenant_chat_widgets` vazio; o único tenant é o id 1 |
| Isolamento | chave inexistente é rejeitada: `GET /api/widget/{chave}/config` → 403 "Widget indisponível ou domínio não autorizado"; chave malformada → 404 |
| Mensagens visitante → VetorLeads → visitante | **não testado**: não há widget ativo. Nenhum dado de teste foi criado |
| WAHA | container `healthy` e não reiniciado; sessão VetorLeads `WORKING` |

## Estado dos containers

Os 21 serviços estão `healthy`. Só os três `abrasilsistema*` foram recriados nesta etapa; os demais mantêm o uptime anterior.

## Pendências comprovadas

1. **VetorChat sem configuração** (bloqueia o item 4):
   - criar e ativar o widget do tenant no VetorLeads (Configurações → Widget de chat), o que gera a `public_key` de 48 caracteres;
   - cadastrar o produto `abrasil` com o domínio `abrasilsistemas.com.br` (Configurações → Produtos de chat);
   - fornecer a chave ao serviço `abrasilsistema`. Hoje o env vem de `gateway/abrasilsistemas/.env.example` (`env_file`), que está versionado e tem a chave vazia. O recomendado é definir no `.env` da infra e referenciar no Compose. Depois disso basta recriar só `abrasilsistema`, sem rebuild.
2. **Google Search Console:** `GOOGLE_SEARCH_CONSOLE_CLIENT_ID` está vazio (o secret está definido). É preciso o client ID do Google Cloud e depois conectar via OAuth pelo painel, usando o callback acima.
3. **`APP_ENV=local`** no `abrasilsistema` (configuração anterior, não alterada).
4. **WAHA:** as sessões `vetoros1-1` e `vetoros1-9` estão `FAILED`. Isso é anterior a esta etapa (WAHA e VetorOS não foram tocados) e fica fora do escopo.
5. Pendências herdadas: o pin do VetorOS no override aponta para uma imagem inexistente, e o gitlink do Desgarrados também (ver `docs/vetorchat-saas-09/executed.md`).
