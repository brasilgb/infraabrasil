# Execução de `correio.md`: AB PROSPECT SaaS multi-tenant, **Fase C1 (activities + automation_executions)**: CONCLUÍDA, COMMIT LOCAL, SEM PUSH

## Identificação

- Data: 2026-10-02 (~13:40–14:40 UTC).
- Repositório `/opt/infra-ab-prospect`, branch `feat/ab-prospect-saas-foundation`.
- Commit inicial: `4e07b81`. Commits da Fase B conferidos: `976a3b3`, `ca3830e`, `4e07b81` (e Fase A `bf48f6a`).
- Commit final: **`51b6442`** "Atividades e execuções de automação tenant-safe do AB Prospect (Fase C1)". Nenhum push.
- `git status`: limpo.

```
51b6442 (HEAD -> feat/ab-prospect-saas-foundation) Atividades e execuções de automação tenant-safe do AB Prospect (Fase C1)
4e07b81 APP_URL de exemplo no domínio oficial do AB Prospect
ca3830e Remove os módulos do site ABrasil do AB Prospect (Fase B, item 9)
976a3b3 Domínio SaaS do AB Prospect: companies, campaigns e prospects (Fase B)
bf48f6a Fundação SaaS multi-tenant do AB Prospect (Fase A)
a515a93 (tag: baseline-prospect-saas-2026-10-02, origin/main, origin/HEAD, main) Fecha CRM-LEAD-02.3 e extensão AB Prospect 1.4.0
```

Rollback: `git reset --hard 4e07b81`.

## Arquivos

**Novos:** `app/Models/Activity.php`, `app/Models/AutomationExecution.php`, `app/Support/WhatsappMessageStatus.php`, `database/migrations/2026_10_02_120000_create_activities_and_automation_executions_tables.php`, `database/factories/ActivityFactory.php`, `AutomationExecutionFactory.php`, `tests/Feature/Tenancy/ActivityDomainTest.php`, `AutomationExecutionDomainTest.php`, `tests/Unit/WhatsappMessageStatusTest.php`.

**Alterados:** `Tenant`, `Company`, `CampaignProspect` (relações), `Campaign`/`CampaignProspect` (hook `deleting`, ver política de exclusão), `LeadActivity` (constantes e regra de ACK passam a delegar a `WhatsappMessageStatus`, sem mudança de comportamento).

Frontend não tocado.

## Activity

**Migration:** `2026_10_02_120000_create_activities_and_automation_executions_tables` (nova; as migrations legadas não foram mexidas).

**Colunas:** `id`, `tenant_id` NOT NULL, `company_id` NOT NULL, `campaign_prospect_id` NULL, `user_id` NULL, `type` (varchar 20, default `note`), `direction` NULL, `status` NULL, `contacted_at` NULL, `next_follow_up_at` (datetime) NULL, `description` (text) NULL, `provider_message_id` NULL (**`utf8mb4_bin`** no MySQL), `message_status` (varchar 50) NULL, `read_at` NULL, timestamps. Mesmos conceitos do `LeadActivity`; nenhum campo novo.

**Índices / uniques:**
- `UNIQUE(tenant_id, id)`: alvo da FK composta de `automation_executions`.
- `UNIQUE(tenant_id, provider_message_id)`: idempotência por tenant. NULL não colide (MySQL e SQLite).
- `activities_company_unread_index (tenant_id, company_id, direction, read_at)`: inbound não lidas por empresa. `EXPLAIN` no MySQL: `key: activities_company_unread_index`, `ref: const,const,const,const`.
- `(tenant_id, company_id, created_at)`: histórico da empresa (equivale ao antigo `(lead_id, created_at)`).
- `(campaign_prospect_id, created_at)`: histórico do prospect.
- Em `campaign_prospects`: novo `UNIQUE(tenant_id, company_id, id)`, alvo da FK de 3 colunas.

**FKs:**
- `(tenant_id, company_id)` → `companies(tenant_id, id)`, CASCADE.
- `(tenant_id, company_id, campaign_prospect_id)` → `campaign_prospects(tenant_id, company_id, id)`, CASCADE. Garante no banco que o prospect é **do mesmo tenant e da mesma empresa**. NULL desliga a verificação (MATCH SIMPLE).
- `tenant_id` → tenants (CASCADE); `user_id` → users (SET NULL, como no legado).

**Política de exclusão (decisão importante):** o desenho inicial usava RESTRICT nos vínculos com prospect e com atividade. No MySQL 8.4 isso **quebrou a exclusão de empresa, campanha e tenant** (`ERROR 1451`): o InnoDB checa RESTRICT linha a linha durante a cascata, e há caminhos em losango (empresa → atividades e empresa → prospects → atividades). Além disso, o MySQL não aceita `ON DELETE SET NULL` em FK composta com `tenant_id NOT NULL`. Por isso todas as FKs compostas usam CASCADE, e a preservação do histórico ficou no model:

| Exclusão | Resultado |
|---|---|
| Tenant ou empresa | Histórico e reservas vão junto (como lead → lead_activities) |
| Prospect (Eloquent) | `CampaignProspect::deleting` põe `campaign_prospect_id = NULL`: atividades ficam no histórico da empresa; reservas do prospect são apagadas |
| Campanha (Eloquent) | `Campaign::deleting` faz o mesmo para as atividades de todos os prospects |
| Atividade (Eloquent) | `Activity::deleting` solta o vínculo (`activity_id = NULL`, como o `nullOnDelete` legado); a reserva continua valendo |
| DELETE por SQL direto | Cascata (perde as atividades do prospect/campanha). **Risco documentado** |

Validado por SQL direto no MySQL (empresa, tenant, campanha, prospect e atividade) e por testes nos dois bancos.

**Tenancy:** trait `BelongsToTenant` (Fases A/B). `tenant_id` fora do `$fillable` (o informado pelo cliente é ignorado), obrigatório e imutável. O tenant é herdado do prospect ou da empresa; criação por `$company->activities()->create()` ou `$prospect->activities()->create()` (a empresa vem do prospect). Consultas por `forCurrentTenant()`/`forTenant()`; binding de rota no tenant atual.

**Validações do domínio:** empresa no tenant; prospect no tenant **e da mesma empresa**; `type` ∈ `TYPES` (note, whatsapp, call, email, meeting); `direction` ∈ inbound/outbound; `message_status` válido. Violação de tenant → `CrossTenantRelationException`; valor inválido → `InvalidArgumentException`.

**`user_id`, decisão:** no legado era o autor (`auth()->id()`), FK `nullOnDelete`, sem validação. Agora, ao gravar, o autor precisa ter **membership ativa no tenant ou ser RootAdmin** (o acesso de RootAdmin a tenant já é explícito e auditado pelo `TenantContext`). Diferente do `owner_id` do prospect (que exige membership): o RootAdmin pode registrar uma nota ao operar um tenant, mas não é responsável comercial. FK só para `users`; o tenant nunca é deduzido do usuário.

**message_status:** a regra do CRM (`PENDING → SERVER → DEVICE → READ → PLAYED`; ERROR só antes de DEVICE; recuperação de ERROR por ACK positivo) foi extraída para `App\Support\WhatsappMessageStatus` e é usada pelo `LeadActivity` e pela `Activity`, **sem duplicação**. `Activity::applyMessageStatus()` devolve `updated`/`idempotent`/`ignored` e aplica um **UPDATE condicional ao status de origem**: uma cópia desatualizada do model ou um ACK simultâneo não fazem o status regredir (no legado era ler e depois gravar). Inbound é ignorada (não tem ACK).

**read/unread:** `markAsRead()` só grava a primeira leitura (`whereNull('read_at')`); outbound não tem leitura interna. Scopes `unreadInbound()` e `conversation()` preservados.

**provider_message_id:**
- Nulo e `""` gravados como NULL; vários NULL convivem.
- `findWhatsappMessage($tenant, $id)` só busca dentro do tenant.
- **Case sensitivity:** a coluna é `utf8mb4_bin`. No legado a coluna usava a collation padrão (`_ci`), então `findWhatsappMessage` não diferenciava maiúsculas, enquanto o lock (`sha1`) diferenciava. O id do WhatsApp é opaco e sensível a maiúsculas; com `_ci`, o novo UNIQUE poderia recusar uma mensagem legítima. A auditoria dos ids reais (consulta só leitura em `abrasilsistemas.lead_activities`) **foi bloqueada** pelo controle de permissões, por ser leitura de produção, e não foi refeita. Ver pendências.

**Lock key:** `Activity::whatsappMessageLockKey($tenant, $id)` = `whatsapp-inbound:{tenant_id}:{sha1(id)}`; `whatsappMessageLock()` = `Cache::lock(chave, 10)`. Teste unitário: tenants diferentes geram chaves diferentes, e "tenant 1 + `2:x`" não colide com "tenant 12 + `x`" (o id vai em sha1). O formato não colide com o lock legado (`whatsapp-inbound:{sha1}`).

**Relações:** `Tenant::activities()`, `Company::activities()`, `CampaignProspect::activities()`, `Activity::tenant/company/campaignProspect/user/automationExecution`.

**Inbound futuro (§21):** a estrutura permite empresa sem prospect. **Nenhum helper de atribuição** foi criado: é comportamento da C2.

## AutomationExecution

**Colunas:** `id`, `tenant_id`, `campaign_prospect_id`, `automation_key` (varchar 150, **`ascii_bin`** no MySQL, como no legado), `executed_at`, `activity_id` NULL. **Sem timestamps**, como o legado (`executed_at` é o instante da reserva).

**Índices:** `UNIQUE(campaign_prospect_id, automation_key)`; `UNIQUE(activity_id)` (uma mensagem pertence a no máximo uma execução, como no legado); `(tenant_id, automation_key)`.

**Decisão do UNIQUE:** sem `tenant_id`. O prospect já pertence a um único tenant, e a auditoria não mostrou razão para redundância.

**FKs:** `(tenant_id, campaign_prospect_id)` → `campaign_prospects(tenant_id, id)` CASCADE; `(tenant_id, activity_id)` → `activities(tenant_id, id)` CASCADE (motivo na política de exclusão acima); `tenant_id` → tenants CASCADE.

**Validações:** prospect e atividade do tenant (model + FK); chave validada no model com o mesmo padrão do legado (`KEY_PATTERN`, 150 caracteres), antes só validada no request da API.

**`reserve(CampaignProspect, key): bool`:** mesmo padrão do legado: **um INSERT**, sem SELECT prévio, capturando `UniqueConstraintViolationException`. O único SELECT é o da herança/checagem de tenant do prospect, que não participa da decisão de unicidade.

**`linkActivity(CampaignProspect, key, Activity): string`:** UPDATE condicional (`activity_id IS NULL`), escopado no tenant e no prospect. Retornos explícitos, em vez do `bool` do legado, para não sobrescrever em silêncio:
- `linked`: vinculou agora;
- `already_linked`: mesma mensagem já vinculada (idempotente);
- `linked_to_another`: a execução já tem outra mensagem, ou a mensagem já é de outra execução (UNIQUE); nada muda;
- `not_reserved`: não há reserva para a chave;
- `not_eligible`: não é WhatsApp outbound **deste prospect** (regra do legado, que exigia o mesmo lead);
- atividade de outro tenant: `CrossTenantRelationException` (o banco também recusa).

**Concorrência (MySQL 8.4 real):** script descartável fora do repositório: 50 prospects; 8 processos PHP independentes (uma conexão cada), liberados no mesmo instante por barreira de relógio, todos chamando `reserve($prospect, 'trial-expired')` nos 50.

```
tentativas: 400
reserved: 50  already: 350
prospects com != 1 reserva: 0
vencedores por processo: 9, 23, 2, 7, 1, 8   (6 dos 8 processos venceram alguma)
SELECT COUNT(*), COUNT(DISTINCT campaign_prospect_id) ... → 50 | 50
```

## Legado

| Item | Situação |
|---|---|
| `Lead` | Ainda usado por ~30 arquivos de `app/` (CRM, API da extensão, WhatsApp, automações, comandos, dashboard) e 34 testes |
| `LeadActivity` | Ainda usado pelos controllers de WhatsApp (inbound, outbound externo, log de atividade, status/ACK), CRM (`LeadController`, `LeadActivityController`, conversa), `DashboardController`, `LeadWhatsappService` e 27 testes |
| `LeadAutomationExecution` | Ainda usado por `LeadAutomationExecutionController`, `WhatsappActivityController`, requests da API, `routes/api.php` e 4 testes |
| Removido | Nada. A regra de ACK saiu do `LeadActivity` para `WhatsappMessageStatus` (constantes mantidas como alias) |
| Temporário por compatibilidade | Tabelas e migrations `lead_activities`/`lead_automation_executions` e seus models: o CRM legado e as integrações n8n/WAHA dependem delas, e a C1 não migra fluxos externos. Removidos quando a C2 levar os fluxos para `Activity`/`AutomationExecution`. Não há camada de compatibilidade duplicada: o domínio novo é independente |

## Testes

Ambiente: cópia descartável no scratchpad (vendor de dev, imagem PHP 8.4.26 do AB Prospect só como runtime, manifest Vite falso) e **MySQL 8.4.11 descartável** `abp-phaseb-mysql` (rede própria `abp-phaseb-net`, volume anônimo, só o banco `abp_test`; conferido antes de usar).

| Execução | Resultado |
|---|---|
| Baseline SQLite (HEAD `4e07b81`) | 1117 passaram (11361 assertions), 0 falhas |
| **Suíte completa SQLite** | **1185 passaram (11554 assertions), 0 falhas** (+68 testes novos, 193 assertions) |
| **Suíte completa MySQL 8.4** | **1185 passaram (11554 assertions), 0 falhas**. Conferido que gravou no MySQL (banco recriado do zero pelos testes, 31 migrations) |
| MySQL: `migrate:fresh` → `migrate:rollback --step=1` → `migrate` → `migrate:reset` → `migrate` | OK; `migrate:status` com as 31 migrations em `Ran` |
| MySQL: `SHOW CREATE TABLE` | FKs compostas, uniques, collations `utf8mb4_bin`/`ascii_bin` conferidos |
| MySQL: DELETE direto (empresa/tenant/campanha/prospect/atividade) | Cascatas sem erro, conforme a política |
| MySQL: concorrência do `reserve()` | 400 tentativas → 50 reservas, 1 por prospect |
| MySQL: `EXPLAIN` não lidas | Usa `activities_company_unread_index`, 4 colunas const |
| Pint (14 arquivos alterados) | PASS |
| PHPStan direcionado (10 arquivos de `app/` e factories) | 0 erros nos arquivos novos. 3 erros em `LeadActivity` (generics em `HasFactory`, `lead()`, `user()`), **preexistentes**: os mesmos 3 no HEAD |
| `git diff --check` / `--cached --check` | Limpo |
| TypeScript/ESLint | Não aplicável (frontend não tocado) |

Cobertura (todos rodam em SQLite e MySQL): criação pela empresa e pelo prospect; `tenant_id` não mass-assignable e imutável; empresa e prospect do mesmo tenant aceitos, de outro tenant recusados (model e **SQL direto**); prospect de outra empresa do mesmo tenant recusado (model e SQL); prospect nullable; autor (membro, membro de outro tenant, inativo, RootAdmin); valores inválidos; `provider_message_id` nulo e vários NULL; duplicado no tenant bloqueado (model e SQL); mesmo id em tenants diferentes; maiúsculas; busca só no tenant; ACK monotônico, idempotente, regressões (DEVICE→SERVER, READ→DEVICE, READ→PENDING, DEVICE→ERROR), cópia desatualizada, ERROR e recuperação, inbound ignorada; `read_at`; scope de conversa; índice de não lidas; lock por tenant; consultas escopadas; relações; exclusões. AutomationExecution: reserva 1ª/2ª, UNIQUE (model e SQL), mesma chave em outro prospect e outro tenant, chave case-sensitive, chaves inválidas, tenant não mass-assignable/imutável, prospect e atividade cross-tenant (model, SQL e `linkActivity`), os 5 resultados de `linkActivity`, relações.

**SQLite × MySQL:** o SQLite checa FKs ao fim do comando, então não reproduz o problema do RESTRICT na cascata (só visto no MySQL, por isso o teste por SQL direto lá). Collations binárias só existem no MySQL (o `=` do SQLite já diferencia maiúsculas). Concorrência real só no MySQL. NULL em UNIQUE e FK composta com NULL (MATCH SIMPLE) se comportam igual nos dois.

## Fases A/B

A suíte completa (Tenant, TenantMembership, TenantApiToken, TenantContext, root access auditado, Company, dedupe por tenant, ProspectProduct, Campaign, CampaignCriteria, CampaignProspect, FKs compostas, BelongsToTenant, binding de rota) passa nos dois bancos. Nenhuma consulta global nova: tudo no domínio novo usa `forTenant`/`forCurrentTenant` ou parte de um pai tenant-owned.

## Segurança

- Banco `ab_prospect` real: **não acessado** (nem leitura).
- Produção **não alterada**. Uma consulta **somente leitura** ao `abrasilsistemas` de produção (formato dos `provider_message_id`) foi tentada e **bloqueada** pelo controle de permissões; não houve acesso.
- `/opt/infra-abrasil`: **nenhum arquivo de infraestrutura alterado**. O único arquivo tocado é este `executed.md`, a pedido do usuário.
- n8n, WAHA, Nginx, SSL, DNS e webhooks: **não alterados**.
- Nenhum restart de container compartilhado, deploy ou push.
- O MySQL descartável `abp-phaseb-mysql`/`abp-phaseb-net` continua de pé para revisão. Remover com `docker rm -f abp-phaseb-mysql && docker network rm abp-phaseb-net`.

## Pendências e riscos

1. **Auditar os `provider_message_id` reais** (só leitura) antes da C2: confirmar que são ASCII, o comprimento máximo e se existem ids que só diferem por maiúsculas. A coluna nova é binária. Se o legado tiver duplicatas por maiúsculas, elas eram tratadas como a mesma mensagem pelo `findWhatsappMessage` (`_ci`), e a importação precisará decidir. Sugestão de consulta: `COUNT(DISTINCT provider_message_id)` × `COUNT(DISTINCT BINARY provider_message_id)` e `MAX(CHAR_LENGTH(...))` em `lead_activities`.
2. **DELETE por SQL direto** de prospect/campanha apaga as atividades com contexto de campanha (a preservação está no model). Exclusão de atividade por SQL apaga a reserva vinculada (o model só solta o vínculo).
3. **`next_follow_up_at`** da atividade usa só o cast `datetime`. O legado normalizava texto sem fuso como horário de Brasília (`Lead::normalizeFollowUpAt`). O `CampaignProspect` da Fase B também não normaliza. Decidir na C2, ao migrar os formulários.
4. **`user_id`** aceita RootAdmin (decisão acima); membership desativada depois não limpa o autor (a regra vale na gravação, como `owner_id`).
5. **`linkActivity`** devolve string em vez de `bool`: quem chamar na C2 deve tratar `linked_to_another` como alerta, não sucesso.
6. Atribuição de inbound ao prospect (§21) e migração dos fluxos WhatsApp/automação/n8n/WAHA para o domínio novo: C2.
7. Pendências anteriores seguem (Fase B §13): produto opcional na campanha, sem soft delete, Lead legado global, páginas institucionais, Fases D/E.

**Fase C1 encerrada. C2 não iniciada.**

---

# Execução de `correio.md`: AB PROSPECT SaaS multi-tenant, **Fase B (domínio)**: CONCLUÍDA, COMMITS LOCAIS, SEM PUSH

Data: 2026-10-02 (~12:30–14:00 UTC, com duas quedas de conexão e um reboot da VPS no meio; o trabalho foi retomado do estado no disco).

## 1. Estado inicial

- Repositório `/opt/infra-ab-prospect`, branch `feat/ab-prospect-saas-foundation`.
- Fase A já commitada em `bf48f6a`. O item 1 do correio (`TenantMembership` com `HasFactory`, `ROLES`, `isOwner()`, `canManageTenant()` e generics PHPDoc) **já está nesse commit**; a pendência do relatório anterior foi resolvida.
- Banco real `ab_prospect` vazio e intocado.

## 2. Commits locais (nenhum push)

| Commit | Conteúdo |
|---|---|
| `976a3b3` | Domínio: `companies`, `prospect_products` tenant-owned, `campaigns`, `campaign_prospects`, trait `BelongsToTenant`, testes |
| `ca3830e` | Item 9: remoção dos módulos do site ABrasil (autorizada explicitamente pelo usuário depois de um bloqueio do classificador de permissões) |
| `4e07b81` | `.env.example`: `APP_URL=https://leads.abrasilsistemas.com.br` |

`git status`: limpo. Rollback: `git reset --hard bf48f6a` (Fase A) ou a tag `baseline-prospect-saas-2026-10-02`.

## 3. Arquivos alterados

**Novos:**
- `app/Models/Company.php`, `Campaign.php`, `CampaignProspect.php`
- `app/Tenancy/BelongsToTenant.php`, `CrossTenantRelationException.php`
- `app/Support/CampaignCriteria.php`
- `database/factories/CompanyFactory.php`, `CampaignFactory.php`, `CampaignProspectFactory.php`, `ProspectProductFactory.php`
- `database/migrations/2026_10_02_110000_create_prospecting_domain_tables.php`
- `tests/Feature/Tenancy/ProspectingDomainTest.php`, `TenantRouteBindingTest.php`

**Alterados:** `ProspectProduct` (tenant-owned), `Tenant` (relações), `ProspectProductController` e rotas (middleware `tenant`), migration de `prospect_products` (+`tenant_id`), 9 testes legados (produtos agora criados num tenant).

**Renomeado:** `2026_10_02_100000_create_saas_tenancy_tables` → `2026_07_16_000000_...`: `prospect_products` (2026-09-28) passa a referenciar `tenants`, que precisa existir antes.

**Removidos (item 9):** ver §5.

## 4. Schema novo

**`companies`** (cadastro mestre do tenant, só dados objetivos):
- `tenant_id` NOT NULL (FK → tenants, cascade).
- Identidade/contato: `company_name`, `contact_name`, `industry`, `category`, `address`, `city`, `state`, `phone`, `whatsapp`, `email`, `website` (2048), `has_website`, `site_status`, `website_checks` (JSON), `instagram`, `source`.
- Maps: `google_place_id` (collation `utf8mb4_bin` no MySQL), `maps_feature_id`, `maps_url`, `latitude`, `longitude`, `rating`, `reviews_count`, `captured_at`.
- `UNIQUE(tenant_id, google_place_id)`: identidade forte dentro do tenant. **NULL não colide** no MySQL nem no SQLite, então empresas sem Place ID convivem; o model converte `""` em NULL.
- Dedupe fraca só com índices (nunca UNIQUE): `(tenant_id, maps_feature_id)`, `(tenant_id, whatsapp)`, `(tenant_id, phone)`, `(tenant_id, email)`, `(tenant_id, company_name, city, state)`. `Company::findDuplicate($tenant, $data)` repete as regras do Lead (Maps → e-mail/WhatsApp/nome+cidade+UF), sempre dentro do tenant, e só encontra: quem chama decide.
- `UNIQUE(tenant_id, id)`: alvo das FKs compostas.

**`prospect_products`:** + `tenant_id` NOT NULL, `UNIQUE(tenant_id, id)`, índice `(tenant_id, active)`. Mantidos: nome, texto de WhatsApp, `trial_days`, texto de expiração, ativo e a configuração de registration check (manual/API, endpoint, autenticação, token criptografado, lookup por WhatsApp/e-mail).

**`campaigns`:**
- `tenant_id`, `prospect_product_id` **opcional**, `name`, `description`, `status` (`draft/active/paused/finished`), `target_audience`, `criteria` (JSON), timestamps.
- FK composta `(tenant_id, prospect_product_id)` → `prospect_products(tenant_id, id)`, `RESTRICT` (produto em uso não é apagado; é desativado).
- `criteria` validado por `App\Support\CampaignCriteria`: `locations[{state, city}]`, `industries`, `categories`, `keywords`, `min_rating`, `min_reviews`, `website` (`any/with/without`). Chave desconhecida é recusada.

**`campaign_prospects`** (empresa numa campanha = estado comercial daquela prospecção):
- `tenant_id`, `campaign_id`, `company_id`, `owner_id` (users, `nullOnDelete`), `status` (mesmo funil do Lead), `score` (0–100), `qualification` (`qualified/disqualified/review`), `qualification_notes`, `qualified_at`, `can_improve`, `opportunity`, `lost_reason`, `next_follow_up_at`, `last_contacted_at`, `trial_started_at`, `trial_ends_at`, `registration_checked_at/_status/_error`, `notes`.
- `UNIQUE(campaign_id, company_id)`, `UNIQUE(tenant_id, id)`.
- FKs compostas `(tenant_id, campaign_id)` → campaigns e `(tenant_id, company_id)` → companies, cascade.

**Decisão: produto opcional na campanha.** No fluxo atual o lead pode não ter produto, e teste/verificação de cadastro só existem quando há produto. Campanha sem produto (ex.: prospecção de serviço sob medida) simplesmente não inicia teste nem consulta API de cadastro.

## 5. Migrations mantidas/removidas

Auditoria das 36 migrations herdadas:

| Grupo | Migrations | Decisão |
|---|---|---|
| Laravel/auth | users, cache, jobs, passkeys, two_factor | Mantidas |
| SaaS | saas_tenancy (renomeada), root_admin, prospecting_domain | Mantidas |
| CRM legado (Lead) | leads e suas 12 alterações, lead_activities (+3), lead_automation_executions (+1) | **Mantidas por enquanto**: o CRM legado ainda roda sobre elas. Substituídas na Fase C (o banco continua vazio, então elas podem ser reescritas em vez de ganhar migrations de conversão) |
| Produtos | prospect_products, registration_check | Mantidas e adaptadas |
| Configuração | settings | Mantida (vira configuração por tenant na Fase C) |
| **Site ABrasil** | `2026_07_19_180000_create_ebook_commerce_tables`, `2026_07_21_120000_create_blog_tables`, `2026_07_22_180000_add_default_blog_categories`, `2026_08_27_120000_create_testimonials_table`, `2026_08_27_120001_create_portfolio_items_table`, `2026_08_29_165826_create_blog_tags_table` | **Removidas**: ebooks, blog, categorias, tags, comentários, depoimentos e portfólio não pertencem ao SaaS. Nenhuma delas tocava tabelas compartilhadas |

Junto com as migrations saiu o código que dependia delas (senão a home e o painel quebrariam):
- models `Blog*`, `Ebook*`, `PortfolioItem`, `Testimonial`;
- 8 controllers admin, `PublicBlogController`, `BlogCommentController`, `EbookMercadoPagoWebhookController`, `MercadoPagoService`, `HtmlSanitizer`;
- páginas React `blog/*`, `admin/{blog,portfolio,testimonials}/*`, `blog-public-layout`, `article-body`, `upload-image`;
- `BlogTest`; config/env do Mercado Pago; exceção de CSRF do webhook;
- links de blog na home, menus, rodapé, sidebar e painel; sitemap sem posts; `area-restrita` manda quem não é RootAdmin ao perfil (antes: blog).

Banco resultante (MySQL descartável): 23 tabelas, nenhuma do site.

**Ficam para depois:** as páginas institucionais estáticas (home, produtos, serviços, sobre, contato, legais, sites para empresas) ainda têm conteúdo e canonicals fixos de `abrasilsistemas.com.br`. Não usam banco; a decisão sobre elas é da fase de remoção do site/landing do AB Prospect.

## 6. Models e relações

- `Tenant`: `companies()`, `prospectProducts()`, `campaigns()`, `campaignProspects()`.
- `Company`: `campaignProspects()`, `campaigns()`, escopo `samePlace()`, `findDuplicate()`; Place ID/FTID derivados do `maps_url` (`MapsPlace`, como no Lead).
- `ProspectProduct`: `campaigns()`; os métodos legados (`options()`, `resolveLegacyProduct()` etc.) continuam servindo só o Lead global do RootAdmin e estão marcados como legado.
- `Campaign`: `prospectProduct()`, `prospects()`, `companies()`, `addCompany()` (idempotente e atômico: `createOrFirst` sobre o UNIQUE).
- `CampaignProspect`: `campaign()`, `company()`, `owner()`, `isClosed()`; valida status, qualificação, score e motivo de perda.

## 7. Estratégia de tenant scoping

Trait `App\Tenancy\BelongsToTenant`, **sem global scope**:
- `Model::forCurrentTenant()` (a partir do `TenantContext`; sem tenant → 403, nunca consulta global) e `Model::forTenant($tenant)` para job/comando/RootAdmin, com o tenant à vista.
- Criação pela relação (`$tenant->companies()->create()`) ou herdada do pai (`$campaign->prospects()->create()`). `tenant_id` fora do `$fillable`; `tenant_id` do cliente é ignorado.
- `tenant_id` obrigatório e **imutável** (evento `saving`).
- Route model binding sempre no tenant atual: registro de outro tenant dá 404 (igual a inexistente); sem tenant resolvido, falha fechado.

Por quê: um global scope esconderia o filtro em jobs e no RootAdmin e tornaria "sem filtro" um acidente silencioso. Aqui a consulta sempre diz de qual tenant quer os dados, e esquecer o escopo numa requisição dá 403/404, não vazamento.

## 8. Integridade cross-tenant

| Garantia | Onde |
|---|---|
| Campaign → Product do mesmo tenant | **Banco** (FK composta) + model (`CrossTenantRelationException`) |
| CampaignProspect → Campaign e Company do mesmo tenant | **Banco** (FKs compostas) + model |
| Prospect herda o tenant da campanha, não do cliente | Model |
| Responsável = membro **ativo** do tenant | Model (o banco não conhece membership; FK para `tenant_memberships` travaria exclusão de usuário) |
| Registro não muda de tenant | Model |
| Acesso por ID / binding | `BelongsToTenant` + middlewares `tenant`/`tenant.api` (Fase A) |
| RootAdmin em tenant alheio | `TenantContext`: só com seleção explícita (`root_access`) e auditoria |

Testes tentam deliberadamente cada relação cruzada, também por SQL direto (o banco recusa mesmo sem passar pelo model).

## 9. Mapa `Lead → Company / CampaignProspect` (para a Fase C)

| Lead | Destino |
|---|---|
| `company_name`, `contact_name`, `industry`, `category`, `address`, `city`, `state`, `phone`, `whatsapp`, `email`, `website`, `has_website`, `site_status`, `website_checks`, `instagram`, `source`, `maps_url`, `google_place_id`, `maps_feature_id`, `latitude`, `longitude`, `rating`, `captured_at` | `Company` (mesmo nome) |
| `reviews` | `Company.reviews_count` |
| `user_id` (responsável) | `CampaignProspect.owner_id` (agora exige membership ativa) |
| `status`, `lost_reason`, `next_follow_up_at`, `last_contacted_at`, `trial_started_at`, `trial_ends_at`, `registration_checked_at`, `registration_check_status`, `registration_check_error`, `notes` | `CampaignProspect` |
| `can_improve`, `opportunity` | `CampaignProspect` (oportunidade depende do que se vende; **revisar**, §13) |
| `prospect_product_id` | `Campaign.prospect_product_id` (o produto é da campanha) |
| `product` (`vetoros/vetorpet`, `Lead::PRODUCTS`) | **Removido** (hardcode VetorOS/VetorPet) |
| `deleted_at` (soft delete) | **Removido** no domínio novo (**revisar**, §13) |
| `created_at`/`updated_at` | Ambos |
| Calculados: `lead_score`, `priority` | `CampaignProspect.score` (persistido, base da IA) |
| Calculados: `phone_line_type`, `whatsapp_line_type`, `whatsapp_status`, `website_type` | Accessors de `Company` (derivam só de dados objetivos) |
| Calculados: `follow_up_state`, `trial_state`, trial/registration helpers, SLA de resposta | `CampaignProspect` |

## 10. Desenho proposto: Activities / WhatsApp / Automations (não implementado)

Como o banco está vazio, a Fase C **reescreve** as migrations `lead_*` em vez de convertê-las.

**`activities`** (substitui `lead_activities`):
- `tenant_id` NOT NULL; `company_id` NOT NULL, FK composta `(tenant_id, company_id)`; `campaign_prospect_id` NULL, FK composta `(tenant_id, campaign_prospect_id)`; `user_id`; `type`, `direction` (inbound/outbound), `status`, `contacted_at`, `next_follow_up_at`, `description`, `provider_message_id`, `message_status`, `read_at`.
- A conversa de WhatsApp é **da empresa** (o número é dela). `campaign_prospect_id` identifica a ação de campanha: outbound enviado a partir de um prospect carrega o id; inbound é atribuído ao prospect aberto mais recente com outbound para a empresa, ou fica NULL (nível empresa) se for ambíguo.
- Idempotência: `UNIQUE(tenant_id, provider_message_id)` (NULL livre) no lugar do índice simples atual, mais o lock de cache com chave **prefixada pelo tenant** (`whatsapp-inbound:{tenant}:{id}`).
- ACK monotônico (`MESSAGE_STATUS_PROGRESSION`), READ e unread mantidos no model; índice de não lidas `(tenant_id, company_id, direction, read_at)`.
- O tenant do webhook vem do token `tenant.api` da integração do tenant, **nunca** de busca global pelo número. Busca por número só dentro do tenant (`Company.whatsapp`).

**`automation_executions`** (substitui `lead_automation_executions`):
- `tenant_id`, `campaign_prospect_id` (FK composta), `automation_key`, `executed_at`, `activity_id` NULL UNIQUE (FK composta).
- `UNIQUE(campaign_prospect_id, automation_key)`; `reserve()` continua sendo insert + captura da violação de UNIQUE (atômico, sem SELECT prévio); `linkActivity()` continua com UPDATE condicional.

Nada do motor de WhatsApp/automação foi alterado nesta fase.

## 11. Domínio oficial

- `.env.example`: `APP_URL=https://leads.abrasilsistemas.com.br`. O código usa `url()`/`APP_URL` (sitemap, canonicals do servidor).
- Nginx/SSL/deploy: não tocados. `ab-prospect.localhost` não aparece no código.
- URLs fixas restantes: só nas páginas institucionais estáticas (§5) e no e-mail de contato.

## 12. Testes e resultados

Ambiente: cópia descartável no scratchpad (`vendor` de dev, imagem PHP 8.4 do AB Prospect só como runtime, manifest Vite falso) e **MySQL 8.4.11 descartável** (`abp-phaseb-mysql`, rede própria `abp-phaseb-net`).

| Execução | Resultado |
|---|---|
| Suíte completa, SQLite, depois do domínio | 1121 passaram, 1 falhou (GD do `BlogTest`, preexistente) |
| Suíte completa, SQLite, depois da remoção do site | **1117 passaram, 0 falharam** (a falha preexistente saiu com o `BlogTest`) |
| MySQL: Tenancy + produtos + trial + registration check + Auth (antes da remoção) | **301 passaram** (conferido que gravaram no MySQL) |
| MySQL: Tenancy + produtos + Auth (depois da remoção) | **144 passaram** |
| MySQL: `migrate` limpo, `rollback --step=1`, `migrate`, `reset`, `migrate` | OK nas duas versões; FKs compostas e UNIQUE conferidos com `SHOW CREATE TABLE` |
| Pint (arquivos alterados) | Limpo |
| PHPStan direcionado | Sem erros |
| `git diff --check` | Limpo |
| `tsc --noEmit` | OK |
| ESLint (TSX alterados) | 5 avisos de ordem de import/hooks, **todos já existentes no HEAD** |

Cobertura do item 13 (`ProspectingDomainTest`, `TenantRouteBindingTest`, `ProspectProductTest`): os 14 casos pedidos, mais `tenant_id` não mass-assignable, registro não muda de tenant, Place ID com maiúsculas/minúsculas, dedupe só no tenant, critérios JSON, exclusão do tenant em cascata, produto em uso não apagável e o banco recusando relações cruzadas por SQL direto.

**SQLite × MySQL (item 17):** as diferenças relevantes foram tratadas:
- collation binária do Place ID só existe no MySQL (o `=` do SQLite já diferencia maiúsculas);
- FKs compostas são aplicadas nos dois (o Laravel liga `foreign_keys` no SQLite);
- NULL em UNIQUE se comporta igual.

A suíte de domínio roda nos dois bancos.

## 13. Riscos e decisões para revisão

1. **Produto opcional na campanha** (§4).
2. **`can_improve`/`opportunity` na campanha**, não na empresa: a oportunidade de venda depende do que se oferece.
3. **Sem soft delete** em `companies`/`campaign_prospects`. O Lead tinha. Se precisar de lixeira, decidir na Fase C.
4. **Responsável**: membership desativada depois não limpa `owner_id` existente; a regra vale na gravação.
5. **Lead legado continua global** (só RootAdmin). O formulário legado lista produtos de todos os tenants via `ProspectProduct::options()`.
6. **Páginas institucionais estáticas** ainda são do site ABrasil (§5). `APP_NAME` no `.env.example` continua "AB Sistemas".
7. Pendências da Fase A que seguem: correio original truncado na Fase E; usuário sem RootAdmin ainda não tem área (Fase D); política de `trial_ends_at`/`paid_until` do tenant.

## 14. Plano proposto para a Fase C

1. Reescrever `lead_activities` → `activities` e `lead_automation_executions` → `automation_executions` conforme §10 (banco vazio).
2. Adaptar a API da extensão (import) para gravar `Company` (`findDuplicate`) + `CampaignProspect` numa campanha, e trocar `prospect.token` por `tenant.api` só nos controllers já tenant-safe.
3. Adaptar WhatsApp inbound/status/outbound/activity e as automações de trial ao tenant e ao `CampaignProspect`, preservando ACK, READ, idempotência e locks.
4. Teste e registration check no `CampaignProspect`, usando o produto da campanha.
5. `settings` por tenant.
6. Remover `Lead`, `Lead::PRODUCTS`, os métodos legados de `ProspectProduct` e as migrations `leads*`.

## Confirmações

- Nenhuma migration no `ab_prospect` real nem em outro banco existente.
- Nenhum deploy, push, restart de infraestrutura compartilhada, Nginx, SSL, WhatsApp ou automação.
- Fase C **não** iniciada.
- O MySQL descartável `abp-phaseb-mysql` e a rede `abp-phaseb-net` continuam de pé para revisão; removê-los com `docker rm -f abp-phaseb-mysql && docker network rm abp-phaseb-net`.

---

# Execução de `correio.md`: AB PROSPECT SaaS multi-tenant, **Fase A (fundação)**: IMPLEMENTADA, SEM COMMIT

Data: 2026-10-02 (~11:50–12:10 UTC).

**Diferença do correio.** O `correio.md` atual traz uma tarefa nova: transformar o AB Prospect em SaaS multi-tenant. A execução anterior era o PERF-VETOROS-02 (o `correio.md` versionado no HEAD `7bfe42d` ainda é o da autenticação Laravel → n8n).

**O correio chegou truncado.** O arquivo termina no meio da Fase E ("33. remover depend", linha 708). Falta o fim da Fase E e provavelmente as instruções de entrega/relatório. O próprio correio pede execução "em etapas pequenas e auditáveis", então **executei só a Fase A (itens 1–9)**. As Fases B–E ficam para os próximos correios.

**Escopo respeitado:**
- só `/opt/infra-ab-prospect`, branch `feat/ab-prospect-saas-foundation` (baseline `a515a93`);
- nenhuma migration no banco `ab_prospect` nem em outro banco existente;
- nenhum container reiniciado, nenhum deploy, nenhum commit/push;
- `gateway/abrasilsistemas` e a infra não foram tocados;
- nenhum secret neste relatório.

**Validação.** Tudo rodou fora dos serviços:
- suíte de testes numa cópia descartável (scratchpad), com `vendor` de dev e a imagem PHP 8.4 do AB Prospect usada só como runtime, com entrypoint sobrescrito;
- migrations num **MySQL 8.4.11 descartável** (container temporário em rede própria, já removido).

Não usei `docker compose run ab-prospect`: a imagem tem o código antigo.

## 1. Baseline (antes de alterar)

Suíte do commit `a515a93`: **1025 passaram, 1 falhou**.
- A falha é `BlogTest > administrator can upload…`: o GD da imagem não suporta o formato da imagem do teste. É do ambiente, não do código, e continua igual depois.
- Para rodar sem build do front-end, a cópia descartável usa um `public/build/manifest.json` falso.

## 2. Fase A: o que foi feito

| Item | Resultado |
|---|---|
| 1. `Tenant` | Relações `memberships()`, `users()` e `apiTokens()` tipadas. `TenantFactory` com estado `inactive()` |
| 2. `TenantMembership` | **Arquivo não alterado** (a escrita foi bloqueada pelo classificador de permissões do Claude Code, ver §6). Ele já tem os papéis `owner/admin/member` e as relações. A regra "owner/admin administra o tenant" ficou em `TenantContext::canManageTenant()`. `TenantMembershipFactory` (`owner()`, `admin()`, `inactive()`) usada via `::new()` |
| 3. `TenantApiToken` | Constantes `PREFIX = 'abp_'` e prefixo exibível de 12 caracteres; `isRevoked()`, `isExpired()` e `isUsable()`; `token_hash` oculto na serialização |
| 4. `is_root_admin` | `User::isRootAdmin()`. **Retirado do `$fillable`**: só muda por `forceFill` no comando ou no seeder. Novas relações `tenants()` e `activeMembershipIn()` (membership ativa em tenant ativo) |
| 5. Fim do `users.role` | Ver §3 |
| 6. Tenant Context | `App\Tenancy\TenantContext` (scoped): `tenant()`, `require()` (403 sem tenant), `membership()`, `apiToken()`, `isRootAccess()`, `canManageTenant()` |
| 7. Middlewares | `tenant` (web) e `tenant.api` (API), ver §4. Os dois rodam antes do `SubstituteBindings`, para permitir bindings escopados ao tenant |
| 8. Serviço de token | `App\Tenancy\TenantApiTokenService`: `issue`, `revoke` (idempotente), `rotate` (transação + `lockForUpdate`, não rotaciona token revogado), `findValid` e `touch` |
| 9. Testes | 39 testes novos em `tests/Feature/Tenancy/`, ver §5 |

**Itens extras, pedidos nos §§4, 6 e 13 do correio:**
- **Criação explícita de tenant e owner:** `App\Tenancy\TenantProvisioner` (slug único).
- **Comandos:**
  - `ab-prospect:root-admin {email} [--revoke]`: só usuário existente, pede confirmação em produção;
  - `ab-prospect:tenant-create {nome} {owner}`;
  - `ab-prospect:tenant-token {slug} {nome} [--expires-in-days]`: mostra o token uma única vez.
- **Auditoria do RootAdmin:** tabela `tenant_root_accesses` (tenant, usuário, IP, user agent, data), só inserção.
- **Inertia:** prop compartilhado `tenant` (`id`, `name`, `slug`, `role`, `root_access`) e tipo TS `CurrentTenant`.

**Migrations (banco vazio; nenhuma executada em banco real):**
- `2026_10_02_100000_create_saas_tenancy_tables`: + `tenant_root_accesses`.
- `2026_10_02_101000_add_root_admin_to_users_table`: sem mudança.
- `2026_07_21_120000_create_blog_tables`: **removida a criação de `users.role`** e o `UPDATE` que promovia todos a admin. As tabelas de blog continuam por enquanto. A decisão sobre as tabelas do site ABrasil (ebooks, blog, testimonials, portfolio) é do item 14 e fica para a Fase B.

## 3. Uso legado de `role`/`isAdmin()`: análise caso a caso

| Uso | Decisão | Por quê |
|---|---|---|
| `routes/web.php`: grupo `admin` (dashboard, leads, WhatsApp, produtos, usuários, blog/portfolio/depoimentos) | `root.admin` | Os dados ainda **não** são tenant-owned. Liberar para owner/admin de tenant exporia os dados de todos os tenants. Cada módulo passa para o middleware `tenant` quando for migrado (Fases B–D) |
| `routes/settings.php`: configurações de leads/WhatsApp (4 rotas) | `root.admin` | Configuração global (`settings`) até virar configuração do tenant |
| `EnsureUserIsAdmin` | Renomeado para `EnsureUserIsRootAdmin` (alias `root.admin`) | O nome agora diz o que a regra faz |
| `area-restrita` | `isRootAdmin()` | Leva ao painel legado |
| `LoginResponse`/`RegisterResponse` | RootAdmin vai ao `dashboard`; os demais vão ao perfil (antes: blog) | Não há área de tenant ainda (Fase D) |
| `CreateNewUser` | Sem papel | **Primeiro usuário não vira admin**. O cadastro não cria tenant nem RootAdmin |
| `UserController::store` | Cria usuário comum (antes: `role=admin`) | RootAdmin só pelo comando |
| `DatabaseSeeder` | Reescrito | O antigo **apagava todos os usuários** exceto o admin e tinha senha padrão. O novo só cria/promove RootAdmin se `ADMIN_EMAIL` e `ADMIN_PASSWORD` existirem no ambiente; não apaga nada e não cria tenant |
| `UserFactory` | `is_root_admin=false` por padrão + `rootAdmin()`; `reader()` removido | Padrão seguro |
| React: 7 pontos (`app-sidebar`, `app-header`, `settings/layout`, `secondary-page-header`, `blog-public-layout`, `welcome`) | `auth.user.is_root_admin` | Os menus mostram exatamente a área que o backend libera |
| Testes legados (≈236 usuários) | `->rootAdmin()` nos testes da área legada; os 15 `reader()` viraram usuário comum (continuam esperando 403) | Mecânico só nos testes. O código de produção foi analisado caso a caso |

## 4. Isolamento

**Web (`tenant`, depois de `auth`):**
- O tenant escolhido fica na sessão e é **revalidado a cada requisição**. Membership desativada, tenant desativado ou RootAdmin rebaixado perdem o acesso na hora.
- Uma sessão forjada apontando para outro tenant é descartada.
- Com uma única membership ativa, ela é escolhida automaticamente. Com várias, a escolha é explícita: `POST /current-tenant` (`DELETE` para sair).
- Tenant inexistente e tenant sem acesso dão o mesmo 403, para não revelar quais IDs existem.

**RootAdmin:**
- Só entra num tenant do qual não é membro com `root_access=1`, e cada entrada é gravada em `tenant_root_accesses`.
- Owner/admin de tenant não consegue usar `root_access`.

**API (`tenant.api`):**
- Fluxo: Bearer → formato `abp_` → SHA-256 → `token_hash` (índice único) → `hash_equals` → não revogado → não expirado → tenant ativo (senão 403 "Tenant inativo.") → `TenantContext`.
- `tenant_id` no corpo, na query ou no cabeçalho é ignorado.
- `last_used_at` é gravado no máximo 1×/min por token, sem alterar `updated_at`.
- O token puro nunca é gravado nem logado.

**Contexto:** os dois middlewares limpam o contexto antes de resolver. Um teste mostrou que, num processo reaproveitado, o tenant da requisição anterior vazava para a seguinte; isso foi corrigido.

**API legada:** os endpoints da extensão e do n8n **continuam** em `prospect.token` (token global `AB_PROSPECT_API_TOKEN`/`settings`). Trocar agora para `tenant.api` daria a cada token de tenant acesso a todos os leads, porque os controllers ainda não filtram por tenant. A troca entra na Fase C (item 17) junto com a adaptação de cada controller. O token global não autentica rotas `tenant.api` (há teste).

## 5. Testes

**Testes novos (`tests/Feature/Tenancy/`, 39 testes):**
- `RootAdminTest`:
  - `is_root_admin` não é mass-assignable nem alterável pelo perfil;
  - owner/admin/member de tenant recebem 403 no painel, nos usuários e nos leads;
  - RootAdmin sem membership tem acesso;
  - a tela de usuários não cria RootAdmin;
  - o comando concede e revoga;
  - o seeder sem credenciais não cria nada e não apaga usuários.
- `TenantApiTokenServiceTest`:
  - só o hash é persistido; o formato é `abp_` + 64;
  - `findValid` rejeita revogado, expirado, desconhecido e malformado;
  - `revoke` é idempotente;
  - `rotate` é testado;
  - o comando de token é testado.
- `TenantApiMiddlewareTest`:
  - token válido resolve o próprio tenant;
  - `tenant_id` forjado (corpo/query/cabeçalho) é ignorado;
  - 401 para ausente, desconhecido, revogado, expirado e token global;
  - 403 para tenant inativo;
  - `last_used_at` respeita o limite de gravação.
- `CurrentTenantTest`:
  - seleção automática e explícita;
  - tenant alheio e sessão forjada;
  - membership/tenant inativos e perda de acesso imediata;
  - RootAdmin explícito com auditoria; owner não consegue `root_access`; rebaixamento do RootAdmin;
  - saída do tenant;
  - prop Inertia `tenant`.
- `TenantProvisioningTest`: owner explícito, slug único, `UNIQUE(tenant_id, user_id)`, comando de criação.

**Alterados:**
- `AuthenticationTest`: login de usuário comum → perfil; novo caso RootAdmin → dashboard.
- `RegistrationTest`: o primeiro usuário **não** vira RootAdmin nem ganha tenant, mesmo enviando `is_root_admin=true`.

**Resultados:**

| Execução | Resultado |
|---|---|
| Suíte completa, SQLite | **1065 passaram, 1 falhou** (a mesma falha de GD do baseline) |
| Tenancy + Auth + UserManagement no **MySQL 8.4.11** descartável | **66 passaram** |
| `migrate` limpo no MySQL 8.4 | Todas as migrations OK; `users` sem `role` e com `is_root_admin` (índice); FKs/UNIQUE conferidos com `SHOW CREATE TABLE` |
| `migrate:rollback --step=2` + `migrate` | OK |
| Pint (arquivos alterados) | Limpo, exceto 2 itens já existentes: `TenantMembership.php` (não editável, §6) e `DashboardTest.php` sem quebra de linha final desde o HEAD |
| PHPStan | 47 erros no baseline → 49. Os 2 a mais são as relações sem tipo genérico em `TenantMembership.php` (§6). Os erros de `env()` no seeder já existiam |
| `tsc --noEmit` | OK |
| ESLint (arquivos React alterados) | 4 erros já existentes, em linhas não alteradas (imports `Tooltip` sem uso em `app-header.tsx`, ordem de import em `welcome.tsx`) |

## 6. Pendências e riscos

1. **`app/Models/TenantMembership.php` não foi alterado.** A escrita foi bloqueada pelo classificador de permissões do Claude Code ("Modify Shared Resources"), provavelmente um falso positivo. O arquivo funciona como está. Se for liberado, convém acrescentar:
   - `use HasFactory`;
   - a constante `ROLES`;
   - `isOwner()` e `canManageTenant()`;
   - os tipos genéricos `@return BelongsTo<Tenant|User, $this>` (resolve os 2 erros do PHPStan e o Pint).
2. **Correio truncado.** Confirme o que vinha depois de "33. remover depend" (fim da Fase E e formato de entrega).
3. **Usuários sem RootAdmin não têm área ainda.** Login de usuário comum vai para o perfil. A verificação de e-mail do Fortify continua redirecionando para `/dashboard` (`config/fortify.php` `home`), que agora dá 403 para quem não é RootAdmin. O cadastro marca o e-mail como verificado, então isso só aparece no fluxo de reenvio. Resolver junto com a área do tenant (Fase D).
4. **Ainda não tenant-owned:** leads, atividades, WhatsApp, automações, produtos e `settings`. Até a Fase C, tudo isso fica só para o RootAdmin.
5. `TenantMembership.role` não tem CHECK no banco; a validação dos papéis será feita na UI de memberships (Fase D).
6. A política de `trial_ends_at`/`paid_until` do tenant não é aplicada (o correio não define). Hoje só `active` bloqueia.

## 7. Próxima etapa sugerida (Fase B)

1. `companies`, `prospect_products` (tenant-owned), `campaigns`, `campaign_prospects` (`UNIQUE(campaign_id, company_id)`), com dedupe por `(tenant_id, google_place_id)`.
2. Decidir quais migrations herdadas ficam (site ABrasil: ebooks, blog, testimonials, portfolio) e consolidar o conjunto antes da primeira execução no `ab_prospect`.
3. Escopo obrigatório pelo `TenantContext` em todos os models tenant-owned, com testes de acesso cruzado por ID.

## Rollback

Nada foi commitado. O estado anterior é o commit `a515a93`. Os arquivos que já estavam pendentes antes desta execução foram mantidos e evoluídos:
- `Tenant`, `TenantMembership` e `TenantApiToken`;
- as 2 migrations de 2026-10-02;
- `User.php`.

A renomeação `EnsureUserIsAdmin` → `EnsureUserIsRootAdmin` está *staged* (`git mv`).

---

# Execução de `correio.md`: PERF-VETOROS-02 — **PREPARADO, SEM DEPLOY**

Data: 2026-09-30 (~18:25–18:45 UTC).

**Diferença do correio:** o `correio.md` atual traz a tarefa nova PERF-VETOROS-02 (primeira otimização controlada). O anterior (commit `cb07eb8`) tratava da autenticação Laravel → n8n.

**Status.** A primeira ação em produção (reload do Nginx da FASE 1) foi **bloqueada pela proteção do ambiente** (classificador de permissões do Claude Code, motivo "Production Deploy"). O usuário escolheu então **"só preparar"**:
- código e testes em container descartável;
- Nginx validado em container descartável;
- medição antes/depois em ambiente isolado;
- comandos de deploy e medição para execução manual.

**Nada foi alterado em produção:** Nginx (`nginx/nginx.conf` e `nginx/conf.d/` intactos), containers, imagens em uso (`infra-abrasil-vetoros:latest` continua `eb1df41c0ecb`) e banco. Sem commit, sem push.

**Números.** Não há números de produção antes/depois: essa medição depende do deploy. Os números abaixo vêm de um ambiente isolado (MySQL 8.4 descartável, massa sintética do tamanho do tenant 2, mesma massa e mesmo harness para as duas versões). Eles demonstram o ganho relativo, não o tempo real em produção.

## PERF-VETOROS-02

### 1. Baseline

**Produção.** Não medida (a FASE 1 foi bloqueada). O que se tem é do PERF-VETOROS-01 (log `combined`, 48 h, sem tempos):
- `/app/orders`: 2.018 requisições, média de 292 KB, máximo de 5.625 KB;
- páginas cheias com ~3.033 KB, dos quais 2.963 KB são o `Customer::all()`;
- sem gzip;
- polling de 192 B executando ~15 consultas.

Os tempos (`$request_time`/`$upstream_response_time`) só passam a existir depois da FASE 1 (seção "Deploy", passo 1).

**Ambiente isolado.** Imagem que está em produção (`eb1df41c0ecb`), massa de 7.208 clientes, 30 equipamentos, 200 OS e 800 `order_logs`. Requisições pelo kernel HTTP do Laravel (sem Nginx/FPM). Mediana de 6 execuções após aquecimento:

| Rota | Mediana | Consultas | Bytes (HTML cheio) | gzip -5 | Pico de memória |
|---|---:|---:|---:|---:|---:|
| `/app` | 791,8 ms | 63 | 6.990.140 | 373.941 | 123,2 MB |
| `/app/orders` | 819,2 ms | 65 | 7.082.360 | 380.115 | 124,5 MB |
| `/app/orders/{id}` | 798,0 ms | 41 | 7.002.044 | 375.304 | 124,0 MB |
| `/app/schedules` | 766,1 ms | 33 | 6.983.220 | 372.587 | 123,7 MB |
| `/app/messages` | 803,4 ms | 32 | 6.982.694 | 372.513 | 123,7 MB |
| polling em `/app/messages` | 100,7 ms | 32 | 189 | 157 | 111,7 MB |
| polling em `/app/orders` | 119,7 ms | 65 | 185 | 155 | 111,9 MB |

Os bytes são maiores que em produção porque a visita cheia embute o JSON *escapado* em HTML e a massa sintética tem campos mais longos. Numa navegação Inertia (XHR) o JSON vai cru: os 2,96 MB medidos no PERF-01. Com 128 MB de `memory_limit`, a versão atual **estourou a memória** no harness de testes, que reaproveita o processo. Foi preciso subir para 1 GB para medir.

### 2. Arquivos alterados

**VetorOS (`gateway/vetoros`, working tree, sem commit)**

| Arquivo | Alteração | Motivo |
|---|---|---|
| `app/Http/Middleware/HandleInertiaRequests.php` | Removido `'customers' => Customer::all()` (e o `use`). Todos os props com consulta viraram closures. `$tenant`, `firstOrCreate` de `Other`/`FiscalSetting` e `CashSession` saíram do corpo do `share()` e foram para dentro das closures | FASES 2 e 3 |
| `tests/Feature/App/SharedInertiaPropsTest.php` (novo) | Visita cheia sem `customers` e com os demais props. O polling parcial devolve só `notifications`+`errors`, com o valor certo, e sem consultas a `customers`, `order_logs`, `equipment`, `plans`, `fiscal_settings`, `cash_sessions`, `tenant_feedback` nem `insert` | Garantir que a regressão não volte. **Falha na versão atual** (2/2) e passa na nova |

**Infra (`/opt/infra-abrasil/staging/perf-vetoros-02/`, novo, fora do `conf.d` vivo)**

| Arquivo | Conteúdo |
|---|---|
| `nginx/nginx.conf.fase1-log` | `nginx.conf` atual + `log_format timed` + `access_log ... timed` (FASE 1, sem gzip) |
| `nginx/nginx.conf` | FASE 1 + gzip (FASE 5) |
| `nginx/conf.d/apps.conf` | `apps.conf` atual + `location /build/assets/` com cache longo nos 2 server blocks do VetorOS (FASE 6). `laravel.inc`/`n8n.inc` são cópias sem mudança, usadas na validação |
| `nginx.conf.diff`, `apps.conf.diff` | diffs contra os arquivos vivos (o `apps.conf.diff` é aplicado com `patch`) |
| `metricas.sh` | somente leitura: p50/p95 de `rt`/`urt`, bytes médios, % gzip, 5xx e 499 por rota. Mostra o polling de `notifications` à parte (campo `pd=`) e roda `docker stats` |

A pasta `staging/` não está no `.gitignore` (aparece como não rastreada).

**Imagem candidata:** `infra-abrasil-vetoros:perf-vetoros-02-candidate` (`sha256:00cc2666…`).
- Build a partir de cópia limpa, pelo mesmo método do CRM-WA-30.1: os 3 arquivos de Empresa não implantados (`CompanyController.php`, `company/index.tsx`, `flash-toast-messages.tsx`) voltaram para o HEAD e o `CompanyControllerTest.php` ficou de fora.
- Conferido: a cópia só difere do HEAD pelos arquivos do CRM-WA-30 (já em produção), pelo middleware e pelo teste novo.
- O build inclui `npm ci` + `vite build`. As tags `latest` **não** foram mexidas.

### 3. Customer::all()

Auditoria do front-end (`resources/js`):
- **Nenhum consumidor do prop compartilhado.** Nenhum `usePage().props.customers` e nenhum `page.props.customers`. O tipo `SharedData` não declara `customers`. Nenhum componente, layout ou hook o lê.
- Páginas que recebem `customers` **como prop da página** recebem do próprio controller, que sobrepõe o compartilhado:
  - `schedules/create-schedule` e `edit-schedule` (`ScheduleController.php:294/362`);
  - `customers/index` (`CustomerController.php:442`, paginado).
- Formulários de OS, venda e contrato buscam clientes em `route('app.customers.search')`.
- Blade (`resources/views`) e testes não dependem do prop.

**Solução: `customers` removido do `share()` por completo.** Nenhuma API nova.

### 4. Shared props / polling

Viraram closures e só são resolvidas quando o prop entra na resposta:
- `subscription` (com o `$user->tenant`);
- `company`, `setting`, `whatsapp`;
- `othersetting` (`Other::firstOrCreate`);
- `cashier` (`CashSession`);
- `fiscalSetting` (`FiscalSetting::firstOrCreate`);
- `performanceAlert`, `customerFeedbackAlert`, `tenantFeedbackRequest`, `taskIndicator`;
- `orderStatus`, `notifications`, `equipments`, `technicals`, `plans`.

Ficaram iguais:
- `auth` (`permissions()` é só array em memória);
- `flash` (já eram closures);
- `quote`, `name`, `app`, `sidebarOpen`;
- `ziggy` (já era closure).

Base técnica (inertia-laravel v2.0.27, `Response::resolveProperties`): `resolvePartialProperties` filtra pelo `only` **antes** de `resolvePropertyInstances` executar as closures.

Mudança semântica, avaliada como segura:
- **Antes:** o `firstOrCreate` de `Other`/`FiscalSetting` rodava em toda requisição, inclusive POST/JSON.
- **Agora:** roda em toda renderização de página Inertia completa.
- Todo uso fora do middleware já trata ausência: `Other::query()->first()?->...` no `ReportController`, e `Other::*()` com `value(...) ?? padrão`.

**Consultas que permanecem no polling** (log de queries no ambiente isolado):
- **Middleware:** 1 consulta (`count` em `messages` do destinatário).
- **O reload parcial ainda executa o controller da página atual inteiro**, e esse é o custo que sobra:
  - `/app/messages`: 3 consultas no total;
  - `/app/schedules`: 4;
  - `/app/orders`: 7 (32 com a massa de 200 OS);
  - `/app` (dashboard): **34**, porque o `DashboardController` calcula ~30 contagens e listas de forma eager.

  Isso fica fora do escopo do PERF-02 (ver §9).

### 5. Nginx

Validado em container `nginx:1.27-alpine` descartável:
- rede isolada;
- a candidata rodando como `vetoros` (php-fpm) contra o MySQL descartável;
- certificados montados `:ro`.

`nginx -t` OK para `nginx.conf.fase1-log` + `conf.d` atual e para `nginx.conf` + `conf.d` de staging.

**Log (FASE 1):**
```nginx
log_format timed '$remote_addr - $remote_user [$time_local] "$request" '
                 '$status $body_bytes_sent "$http_referer" "$http_user_agent" '
                 'host=$host rt=$request_time urt=$upstream_response_time '
                 'rl=$request_length ce=$sent_http_content_encoding pd=$http_x_inertia_partial_data method=$request_method uri=$request_uri';
access_log /var/log/nginx/access.log timed;
```
Mantém todos os campos do `combined` (os parsers atuais continuam funcionando) e acrescenta host, tempos, tamanho da requisição, Content-Encoding e o `X-Inertia-Partial-Data`, que separa o polling. Linha real do teste: `"GET /login HTTP/1.1" 200 20200 ... host=vetoros.localhost rt=0.029 urt=0.029 rl=109 ...`.

**gzip (FASE 5, bloco `http`, vale para todos os sites):**
- `gzip on; gzip_comp_level 5; gzip_min_length 1024; gzip_vary on; gzip_proxied any;`
- `gzip_types`: JSON, JavaScript (`application/javascript`, `text/javascript`), CSS, SVG, XML (`application/xml`, `text/xml`), `text/plain` e `application/manifest+json`. `text/html` já entra por padrão.
- JPEG/PNG/WebP ficam de fora, e `text/event-stream` também.

**Cache (FASE 6), só no VetorOS (server `vetoros.localhost` e `vetoros.com.br`):**
```nginx
location /build/assets/ { alias /var/www/vetoros/build/assets/; try_files $uri =404; expires 1y; add_header Cache-Control "public, immutable"; }
```
Todos os 245 arquivos de `build/assets/` da candidata têm hash no nome. O `build/manifest.json` (sem hash) continua em `location /build/`, sem cache longo.

Resultado do teste HTTP no Nginx descartável:

| Recurso | Sem gzip | Com `Accept-Encoding: gzip` | Headers |
|---|---:|---:|---|
| `/login` (HTML) | 140.830 B | 20.187 B, `Content-Encoding: gzip` (-86 %) | `Vary: Accept-Encoding` |
| `vendor-*.js` | 2.285.858 B | 748.912 B, gzip (-67 %) | `Cache-Control: max-age=31536000` + `public, immutable`. `Expires` +1 ano |
| `app-*.css` | 200.767 B | 30.768 B, gzip (-85 %) | idem |
| `build/manifest.json` | 155.490 B | 12.972 B, gzip | **sem** cache longo (correto) |
| `auth-images-*.jpg` | 503.990 B | 503.990 B, **sem** gzip (correto) | cache longo |

### 6. Testes

Tudo em containers descartáveis a partir da imagem candidata, com `composer install` (dev) e MySQL 8.4 descartável em rede isolada (`perf02-net`), removidos ao final. Os testes não rodam em SQLite: a migration `2026_07_07_120000_create_or_update_commercial_plans` falha nele.

| Comando | Resultado |
|---|---|
| `php artisan test tests/Feature/App/SharedInertiaPropsTest.php` (candidata) | **2 passaram (38 asserções)** |
| o mesmo teste na imagem de produção atual | 2 falharam (esperado: prova que o teste pega a regressão) |
| `php artisan test` completo, candidata | 36 falharam, **264 passaram** (1.329 asserções) |
| `php artisan test` completo, imagem de produção atual | 36 falharam, 262 passaram (1.291 asserções) |
| comparação das falhas | **lista idêntica**, todas pré-existentes: FollowUp 6, Order 6, TechnicianScheduleApi 10, OsController 3, Part 2, Permissions 2, WhatsAppSend 2, ProcessCustomerFeedbackRequests 2, Payment 1, QualityIndicator 1, Schedule 1 |
| `vendor/bin/pint --test` (middleware + teste) | PASS |
| `npx tsc --noEmit` | 1 erro **pré-existente** e sem relação com a mudança (`resources/js/ssr.tsx`: módulo `ziggy-js` não encontrado). Nenhum arquivo de front-end foi alterado |
| build do front-end (`vite build` no `docker build`) | OK |
| `git diff --check` | OK |

Os testes manuais (login, dashboard, clientes, busca de clientes, criação/edição de OS, agenda, mensagens, vendas, contratos, notificações e navegação) **não foram feitos**: dependem do deploy. Estão no checklist do deploy.

### 7. Métricas depois

Ambiente isolado, mesma massa e mesmo harness do §1, imagem candidata:

| Rota | Mediana | Consultas | Bytes (HTML cheio) | gzip -5 | Pico de memória |
|---|---:|---:|---:|---:|---:|
| `/app` | 81,2 ms | 61 | 207.709 | 21.686 | 63,3 MB |
| `/app/orders` | 84,5 ms | 60 | 300.816 | 28.399 | 64,1 MB |
| `/app/orders/{id}` | 67,8 ms | 38 | 223.632 | 23.356 | 64,0 MB |
| `/app/schedules` | 59,1 ms | 32 | 200.831 | 20.438 | 63,8 MB |
| `/app/messages` | 57,0 ms | 31 | 200.265 | 20.338 | 63,8 MB |
| polling em `/app/messages` | 2,8 ms | 3 | 189 | 157 | 58,3 MB |
| polling em `/app/orders` | 21,4 ms | 32 | 185 | 155 | 58,8 MB |

As métricas de produção (`request_time`, `upstream_response_time`, bytes, Content-Encoding, 5xx, 499 e `docker stats`) ficam para depois do deploy, com `staging/perf-vetoros-02/metricas.sh`.

### 8. Regressões

- Nenhuma regressão na suíte: as 36 falhas são idênticas às da imagem em produção.
- Sem teste manual (depende do deploy).
- **Pontos de atenção para o deploy:**
  1. o gzip vale para todos os sites do Nginx (VetorPet, CRM, n8n, WAHA, phpMyAdmin);
  2. o gzip sobre HTTPS em páginas com token CSRF tem o risco teórico conhecido (BREACH), que é baixo aqui;
  3. o `apps.conf` tem mudanças não commitadas (waha/phpMyAdmin) já ativas. Por isso a FASE 6 usa `patch` sobre o arquivo vivo, e não cópia.

### 9. Próxima etapa (PERF-VETOROS-03)

Com base só no que sobrou nas medições:
1. **`DashboardController`**: 34 consultas executadas a cada polling de 60 s enquanto o usuário está no dashboard, e ~30 contagens em cada visita. Candidato a props lazy e/ou cache curto por tenant.
2. **Polling em telas com controller pesado**: `/app/orders` ainda faz 32 consultas por poll com 200 OS. Opções:
   - um endpoint leve dedicado às notificações; ou
   - closures nos props do `OrderController`.
3. **Resíduo da página**: ~200 KB crus por visita cheia, incluindo o Ziggy duplicado (`@routes` + prop `ziggy`, ~80 KB). Com gzip, ~20 KB.
4. Os demais itens do PERF-01 (`order_logs`, `customerFeedbackAlert`, `APP_ENV`/`optimize`, swap, FPM) só depois das métricas reais de produção.

| Métrica | Antes | Depois | Variação |
|---|---:|---:|---:|
| tamanho `/app/orders` (isolado, HTML cheio) | 7.082.360 B | 300.816 B | −95,8 % |
| request_time `/app/orders` (isolado, mediana no kernel) | 819,2 ms | 84,5 ms | −89,7 % |
| upstream time `/app/orders` (produção) | pendente (FASE 1) | pendente (deploy) | — |
| tamanho página padrão (isolado, `/app/messages`) | 6.982.694 B | 200.265 B | −97,1 % |
| polling notifications (isolado, `/app/messages`) | 100,7 ms | 2,8 ms | −97,2 % |
| queries do polling (isolado, `/app/messages` / `/app/orders`) | 32 / 65 | 3 / 32 | −91 % / −51 % |
| transferência gzip (Nginx descartável) | `/login` 140.830 B · `vendor.js` 2.285.858 B · `app.css` 200.767 B | 20.187 B · 748.912 B · 30.768 B | −86 % · −67 % · −85 % |

## Deploy (para execução manual, nesta ordem)

Execute em `/opt/infra-abrasil`. Pare em qualquer `nginx -t` que falhe.

```sh
cd /opt/infra-abrasil

# 0. Pontos de rollback
docker tag infra-abrasil-vetoros:latest infra-abrasil-vetoros:rollback-perf-vetoros-02      # hoje eb1df41c0ecb
cp nginx/nginx.conf backups/nginx.conf.pre-perf-vetoros-02
cp nginx/conf.d/apps.conf backups/apps.conf.pre-perf-vetoros-02

# 1. FASE 1: log com tempos (sem gzip), para o baseline de produção
cp staging/perf-vetoros-02/nginx/nginx.conf.fase1-log nginx/nginx.conf
docker compose exec nginx nginx -t && docker compose exec nginx nginx -s reload
docker compose logs --since 2m nginx | grep ' rt=' | tail -3            # deve mostrar rt= urt= ce= pd=
#    deixar algumas horas de uso normal e então:
sh staging/perf-vetoros-02/metricas.sh 3h | tee backups/perf02-antes.txt

# 2. FASES 2–4: deploy do VetorOS (imagem candidata já construída e testada)
docker tag infra-abrasil-vetoros:perf-vetoros-02-candidate infra-abrasil-vetoros:latest
docker tag infra-abrasil-vetoros:perf-vetoros-02-candidate infra-abrasil-vetoros-worker:latest
docker tag infra-abrasil-vetoros:perf-vetoros-02-candidate infra-abrasil-vetoros-scheduler:latest
docker compose up -d --no-deps --no-build vetoros vetoros-worker vetoros-scheduler
docker compose ps vetoros vetoros-worker vetoros-scheduler                 # aguardar os três "healthy"
docker compose exec nginx nginx -s reload
docker compose exec vetoros grep -c "Customer::all" app/Http/Middleware/HandleInertiaRequests.php   # esperado: 0

# 3. FASES 5–6: gzip + cache dos assets
patch nginx/conf.d/apps.conf < staging/perf-vetoros-02/apps.conf.diff
cp staging/perf-vetoros-02/nginx/nginx.conf nginx/nginx.conf
docker compose exec nginx nginx -t && docker compose exec nginx nginx -s reload

# 4. Validação
curl -s -o /dev/null -D - -H 'Accept-Encoding: gzip' https://vetoros.com.br/login | grep -iE 'content-encoding|vary'
A=$(docker compose exec -T nginx sh -c 'ls /var/www/vetoros/build/assets | grep "^app-.*\.js$" | head -1')
curl -s -o /dev/null -D - -H 'Accept-Encoding: gzip' "https://vetoros.com.br/build/assets/$A" | grep -iE 'content-encoding|cache-control|expires'
curl -s -o /dev/null -D - https://vetoros.com.br/build/manifest.json | grep -i cache-control || echo "manifest sem cache longo (ok)"
curl -s -o /dev/null -w '%{http_code}\n' https://vetorpet.com.br/login https://abrasilsistemas.com.br/login   # outros sites seguem 200
#    checklist manual: login, dashboard, clientes, busca de clientes, criar/editar/ver OS, agenda (criar/editar),
#    mensagens, vendas, contratos, sino de notificações (aguardar 60 s), navegação entre páginas
docker compose logs --since 30m vetoros | grep -iE 'error|exception' | tail -20
#    depois de algumas horas de uso:
sh staging/perf-vetoros-02/metricas.sh 3h | tee backups/perf02-depois.txt
```

Rollback:
```sh
for s in vetoros vetoros-worker vetoros-scheduler; do docker tag infra-abrasil-vetoros:rollback-perf-vetoros-02 infra-abrasil-$s:latest; done
docker compose up -d --no-deps --no-build vetoros vetoros-worker vetoros-scheduler
cp backups/nginx.conf.pre-perf-vetoros-02 nginx/nginx.conf
cp backups/apps.conf.pre-perf-vetoros-02 nginx/conf.d/apps.conf
docker compose exec nginx nginx -t && docker compose exec nginx nginx -s reload
```

Parado aqui, conforme o correio: nenhuma outra otimização foi iniciada.

---

# Execução de `correio.md`: PERF-VETOROS-01, auditoria geral de performance do VetorOS

Data: 2026-09-30 (~17:40 UTC). **Somente auditoria.** Nenhum código, configuração, container, índice, dado ou arquivo de produção foi alterado. Nada foi reiniciado. Nenhum secret foi impresso. Todas as consultas ao MySQL foram `SELECT`/`SHOW`/`EXPLAIN` ou leituras do `performance_schema`/`sys`. As medições em PHP foram feitas com `artisan tinker` em modo só leitura, com um tenant só, sem gravar nada.

**Diferença do correio:** o `correio.md` anterior (commit `cb07eb8`) tratava da autenticação Laravel → n8n do abrasilsistema. O atual é o PERF-VETOROS-01, uma tarefa nova.

## Resumo executivo

A lentidão do VetorOS **não vem da VPS nem do MySQL**. As duas causas principais estão na aplicação e no Nginx:

1. **Todas as páginas Inertia levam a base inteira de clientes junto.** `HandleInertiaRequests::share()` faz `Customer::all()`. Para o tenant 2 são 7.208 clientes, **2,96 MB de JSON** em cada navegação, ~500 ms de CPU PHP e ~72 MB de memória. No front-end não foi encontrado nenhum consumidor desse prop: as telas buscam clientes em `/app/customers/search`.
2. **O Nginx não comprime nada** (sem `gzip`). Essas páginas de ~3 MB e o `vendor.js` de 2,2 MB viajam sem compressão. Em 48 h foram 584 respostas acima de 500 KB, somando **1,68 GB**. Com gzip, os 2,96 MB de clientes caem para 283 KB (-90 %).

Há ainda um agravante: um polling de 60 s (`router.reload({only: ['notifications']})`) em todas as telas executa o middleware inteiro a cada chamada, com ~15 consultas, incluindo `Customer::all()` e dois *full scans* em `order_logs`. Isso só para devolver 192 bytes.

---

## 1. Estado atual da VPS

| Item | Valor |
|---|---|
| SO / kernel | Debian 13 (trixie), 6.12.107+deb13-amd64 |
| vCPUs | 2 |
| RAM | 7,8 GiB: 4,1 usada, 3,6 disponível, 3,8 em buff/cache |
| Swap | **nenhuma** (0 B) |
| Load average | 0,42 / 0,27 / 0,28 (uptime de 12 dias) |
| Disco `/` | 99 G, 39 G usados (41 %). Inodes em 14 % |
| CPU (`vmstat` 5 s) | 92–96 % idle, **wa = 0** |
| PSI | cpu some avg10 ≈ 3,6 %. memory e io: 0,00 |
| Maiores consumidores | mysqld 630 MB RSS, WAHA ~355 MB, n8n ~340 MB, dockerd 188 MB. php-fpm ~60–75 MB por worker |

**Pressão de memória:** houve **OOM global** no kernel.
- 27/09 22:42: `mysqld` morto (anon-rss 735 MB). Daí o `RestartCount=1` do MySQL e o uptime de 2 dias.
- 28/09 14:11: `node` do **WAHA** morto com **2,27 GB** anon-rss.

Não há swap e nenhum container tem limite de memória. Isso não explica a lentidão do dia a dia, mas causa quedas e é risco de estabilidade.

Sem gargalo de I/O ou de CPU no momento.

## 2. Estado dos containers

`docker compose ps`: todos os 15 serviços estão `Up (healthy)`. O `vetoros`, o `-worker` e o `-scheduler` estão no ar há 7 h (recriados em 30/09 11:06).

| Container | CPU | Memória | Restarts | Observação |
|---|---|---|---|---|
| vetoros | 0,01 % | 101 MB | 0 | Net I/O de **1,96 GB saída** em 7 h |
| vetoros-worker | 0 % | 39 MB | 0 | `queue:work database --sleep=3` |
| vetoros-scheduler | 0,06 % | 45 MB | 0 | `schedule:work` |
| mysql | 0,76 % | 616 MB | **1** (OOM de 27/09) | |
| redis | 0,36 % | 8 MB | 0 | quase sem uso (ver §6) |
| nginx | 0 % | 17 MB | 0 | |
| waha | 0,07 % | 407 MB | 0 | já chegou a 2,27 GB (OOM) |
| n8n | 0,28 % | 400 MB | 0 | |
| vetorpet-worker | 0 % | 34 MB | **14** | reinícios em cascata do OOM de 27/09 |

- **Limites:** nenhum container tem `mem_limit` ou `cpus` (`HostConfig.Memory=0`, `NanoCpus=0`).
- **Logs:** todos com `json-file` em `max-size 10m × 3`. O total dos logs Docker é 48,5 MB. Sem crescimento anormal.
- Nada foi limpo.

## 3. Estado do Nginx

`nginx/nginx.conf` + `conf.d/apps.conf` + `conf.d/laravel.inc`:

| Item | Situação | Impacto |
|---|---|---|
| **gzip** | **ausente** (nem `gzip on`, nem `gzip_types`) | **Crítico**: JSON/HTML/JS/CSS trafegam sem compressão. Confirmado via `curl` (sem `Content-Encoding`) |
| Cache de estáticos | `/build/` é servido sem `Cache-Control` ou `expires`, apesar do nome com hash | Revalidação ou re-download em revisitas |
| FastCGI | `fastcgi_pass` para `vetoros:9000` via variável (DNS em runtime), `fastcgi_buffers 8 32k`, `buffer_size 32k` | Respostas de 3 MB passam de 256 KB de buffer e vão para arquivo temporário |
| keepalive | padrão (75 s). Sem `upstream` com keepalive para FastCGI | baixo |
| HTTP/2 / TLS | `http2 on`. Sem `ssl_session_cache` | baixo (handshake completo em conexões novas: 1ª requisição 320 ms contra 40 ms nas seguintes) |
| Redirects | `http→https` e `www→apex`: um salto só, correto | nenhum |
| Timeouts | padrão | nenhum |
| Logs | formato `combined`, **sem `$request_time` nem `$upstream_response_time`** | não dá para medir latência pelo log. Precisa entrar antes do PERF-02 |

Tamanhos por rota do VetorOS (log do Nginx, 48 h, 32.938 linhas):

| Rota | Requisições | Média | Máximo |
|---|---|---|---|
| `/app/orders` | 2.018 | 292 KB | 5.625 KB |
| `/app/orders/{id}` | 1.888 | 278 KB | 3.051 KB |
| `/app` (dashboard) | 196 | 349 KB | 5.525 KB |
| `/app/schedules` | 49 | **2.917 KB** | 3.039 KB |
| `/app/messages` | 28 | 2.498 KB | 3.041 KB |
| `/app/improvement-requests`, `/app/parts`, `/app/reports` | — | **~3.033 KB** | — |

Quase toda página cheia tem **~3,03 MB**: é o "piso" dos props compartilhados. As médias de `/app/orders` e `/app/orders/{id}` ficam baixas porque ~1.750 requisições são o polling de 60 s (192 bytes). Houve **13 respostas 499** (cliente desistiu) e **1 502** em `/app/orders`.

## 4. Estado PHP / OPcache

Container `vetoros` (`php:8.4-fpm`, PHP 8.4.26):

| Item | Valor | Comentário |
|---|---|---|
| `php.ini` | **inexistente** (não há `php.ini-production` copiado) | valores padrão de compilação |
| `memory_limit` | 128M | uma requisição com `Customer::all()` + JSON chega a ~72 MB |
| `max_execution_time` | 0 | |
| `display_errors` | STDOUT | padrão de desenvolvimento |
| `zend.assertions` | **1** | produção usa -1 |
| OPcache | habilitado. `memory_consumption=128`, `interned_strings_buffer=8`, `max_accelerated_files=10000`, `validate_timestamps=On`, `revalidate_freq=2`, JIT desabilitado | razoável. `validate_timestamps=0` daria um ganho pequeno. Status em runtime não foi obtido (não há endpoint de status e nada foi criado) |
| FPM | `pm=dynamic`, **`max_children=5`**, start 2, min/max spare 1/3 | com ~0,5 s de CPU por página, 5 páginas simultâneas já enchem o pool. Hoje não há evidência de saturação: nenhum aviso `max_children` nos logs de 72 h |
| phpredis | **ausente** | apesar de `REDIS_CLIENT=phpredis` |

## 5. Estado Laravel

`php artisan about` (leitura):

| Item | Valor |
|---|---|
| Laravel | 12.69.2 |
| **APP_ENV** | **local** (`.env` da infra: `APP_ENV=local`; padrão do compose: `${APP_ENV:-local}`) |
| APP_DEBUG | false |
| LOG_LEVEL | **debug** |
| Config cache | **NOT CACHED** |
| Route cache | **NOT CACHED** (374 rotas) |
| Event cache | **NOT CACHED** |
| View cache | cached (compilado sob demanda) |
| Cache / Session / Queue | `database` / `database` / `database` |
| Redis | configurado, mas não usado pelo VetorOS |
| Jobs pendentes | 0 |
| Jobs com falha | **860** (842 × SMTP `451 Ratelimit` + 18 × `550`, todos de 20/09 entre 23:18 e 23:39). Ocupam 14,5 MB |
| Sessões no banco | 5 |
| `storage/logs` | vazio (4 KB). O `storage` não é volume, então o log se perde a cada recriação |
| Sessões/cache em arquivo | irrelevante (4 KB / 8 KB) |

O `docker-entrypoint.sh` só copia o build do front. Não roda `config:cache`, `route:cache` nem `event:cache`. Nenhum `env()` fora de `config/` foi encontrado, então `config:cache` é seguro.

## 6. Estado Redis

Redis 7 com 988 KB usados, 84 mil comandos no total e 0 ops/s. O VetorOS não usa Redis: cache, sessão e fila estão em `database`, e o `phpredis` nem está instalado. **Não há gargalo aqui**, e migrar para Redis não é prioridade (ver §10).

## 7. Estado MySQL

MySQL 8.4.11, uptime de 2,79 dias (desde o OOM de 27/09).

| Métrica | Valor |
|---|---|
| Threads_connected / running | 4 / 2 |
| Max_used_connections / max_connections | 8 / 151 |
| Buffer pool | 128 MB (5.909 de 8.192 páginas livres) |
| Hit ratio | 1.906 leituras de disco / 328,5 M requests = **99,9994 %** |
| Created_tmp_disk_tables | 0 |
| Slow_queries | 0 (**`slow_query_log=OFF`**, `long_query_time=10`) |
| Select_full_join / Select_scan | 200 / 73.652 |
| Handler_read_rnd_next | **95,7 M** (quase tudo em `order_logs`, ver §9) |
| Tamanho | vetoros 28,1 MB · mysql 8,3 · vetorpet 2,6 · abrasilsistemas 1,4 |

O banco cabe inteiro em memória. **Configuração de MySQL não é o problema.**

Tempo de banco do `vetoros_user` em 2,79 dias: `Execute` 265 s + `Prepare` 66 s + `Close` 4 s, em **584.899 consultas**. Laravel usa *prepared statements* no servidor (3 idas por consulta). Além disso, `BEGIN`/`COMMIT` foram 80.399 cada, vindos do polling da fila `database` a cada 3 s (custo total ~11 s).

O digest por texto de SQL não registra consultas preparadas (protocolo binário). Por isso a análise por consulta foi feita pelo I/O por índice (`table_io_waits_summary_by_index_usage`, `sys.schema_tables_with_full_table_scans`).

## 8. Análise das principais tabelas (vetoros)

| Tabela | Linhas | Dados + índice | Nota |
|---|---|---|---|
| failed_jobs | 860 | 14,6 MB | lixo de 20/09 (SMTP) |
| customers | 7.208 no tenant 2 (17 no tenant 7, ≤4 nos demais) | 1,7 MB | índice `(tenant_id, customer_number)` |
| order_logs | 6.294 | 3,1 MB | índices só em `(order_id, created_at)` e `user_id`. **Nenhum em `action`** |
| operational_audits | 4.829 | 2,4 MB | |
| orders | 638 no tenant 2 | 2,0 MB | 18 índices, inclusive `(tenant_id, service_status, created_at)` |
| order_status_history | 2.704 | 0,5 MB | |
| equipment | 18 no tenant 2 | — | |
| plans | 6 | — | |

## 9. Problemas de queries encontrados

Leituras por índice/tabela desde o restart do MySQL (2,79 dias):

| Tabela · índice | Linhas lidas | Latência | Leitura |
|---|---|---|---|
| `customers · customers_tenant_number_idx` | **51,86 M** | **93,8 s** | 51,86 M / 7.208 ≈ **7.195 cargas completas** dos clientes do tenant 2 = `Customer::all()` a cada requisição |
| `order_logs · (sem índice)` | **93,94 M** | 27,2 s | 93,94 M / 6.294 ≈ **14.925 full scans** ≈ 2 por requisição = `commercialPerformanceAlert()` (`WHERE action=? AND created_at BETWEEN`) → `EXPLAIN`: `type=ALL`, 6.294 linhas, filtered 1,11 % |
| `orders · orders_tenant_id_order_number_unique` | 19,86 M | 19,8 s | ≈ 31 mil varreduras dos 638 pedidos do tenant ≈ 4 por requisição = `customerFeedbackAlert()` (1 `exists` + 4 `count` sobre `customer_feedback_*`, sem índice) |
| `orders · orders_tenant_status_created_idx` | 5,47 M | 7,3 s | `personalTaskIndicator()` + `orderStatus` |
| `cache · PRIMARY` | 160 mil | 1,6 s | cache em banco |
| `jobs · jobs_queue_index` | 80 mil | 0,7 s | polling da fila |

As contagens batem com cerca de 7.200–7.500 execuções do middleware em 2,8 dias. **Cada consulta isolada é rápida.** O banco lê só ~13 ms por carga de clientes. O custo vem da repetição e do que o PHP faz depois (§10).

Medições diretas, só leitura (tinker no container `vetoros`, CLI sem OPcache):

| Operação | Resultado |
|---|---|
| `Customer` do tenant 2 → `get()` | 7.208 linhas, **72 ms** (banco + hidratação) |
| `->toJson()` | **425 ms**, **2.963 KB** (gzip: **283 KB**), pico de **72 MB** de memória |
| `OrderLog` 29 dias `budget_follow_up_sent` | 4,1 ms (full scan, 0 linhas) |
| Ziggy (374 rotas) | 40 KB (gzip 4 KB), 42 ms |
| `orderStatus` (status 3) | 8 linhas, 24 KB, 2 ms |

Referência: `GET /login` (sem autenticação e sem esses props) tem TTFB de **~40 ms**.

## 10. Problemas de código encontrados

### C1 — `Customer::all()` compartilhado em toda página *(CRÍTICO)*
1. `app/Http/Middleware/HandleInertiaRequests.php:424`
2. `share()` → `'customers' => $user ? Customer::all() : []`
3. Carrega e serializa todos os clientes do tenant em toda resposta Inertia, **inclusive nos reloads parciais** (o valor não é closure, então é calculado mesmo quando só `notifications` é pedido).
4. +2,96 MB por página, ~0,5 s de CPU e ~72 MB de memória por request, e o navegador precisa parsear 3 MB de JSON (o Inertia também grava isso no `history.state`).
5. `customers_tenant_number_idx` com 51,86 M linhas lidas. Páginas "vazias" com ~3.033 KB no Nginx. Medição de 2.963 KB. `grep` no front não achou nenhum `usePage().props.customers`: os formulários usam `route('app.customers.search')`.
6. Remover do `share()` (ou, no mínimo, `Inertia::optional(fn () => …)`). Confirmar antes com `tsc` + `grep` completo.

### C2 — Props compartilhados calculados de forma "eager" + polling de 60 s *(ALTO)*
1. `HandleInertiaRequests.php:326–455` e `resources/js/components/app-sidebar-header.tsx:69-73`
2. `share()`, `personalTaskIndicator()`, `commercialPerformanceAlert()`, `customerFeedbackAlert()`, `tenantFeedbackRequest()`
3. A cada request (página cheia ou polling `only: ['notifications']`) rodam: `Other::firstOrCreate`, `FiscalSetting::firstOrCreate`, `CashSession`, `Company`, `Setting`, `WhatsappMessage`, `Equipment::all()`, `Customer::all()`, `technicals`, `Plan::all()`, `orderStatus`, 4 consultas de tarefas com `whereDoesntHave(logs … whereDate)`, 2 carregamentos de `order_logs` com `whereHas('order')` + eager load, e 5 consultas de feedback. São ~20 consultas, e a maior parte do resultado é descartada no polling.
4. Cada aba aberta gera 1 request por minuto com o custo de uma página cheia (menos o JSON). ~1.750 dos ~2.000 hits em `/app/orders` em 48 h foram esse polling.
5. Log do Nginx (`/app/orders?page=1 … 200 192`, a cada ~60 s por cliente). Contagens da §9.
6. Envolver cada prop pesado em `fn () =>` (o Inertia só avalia o que vai na resposta). Usar `Inertia::defer`/`optional` para os alertas. Cache curto (1–5 min) por tenant/usuário para `taskIndicator`/`performanceAlert`/`customerFeedbackAlert`.

### C3 — `commercialPerformanceAlert()` faz full scan em `order_logs` *(MÉDIO após C2)*
1. `HandleInertiaRequests.php:136-148`
2. Filtro por `action` + `created_at` sem índice. `OrderLog` não tem `TenantScope`: a restrição por tenant depende só do `whereHas('order')`.
3. O custo cresce linearmente com `order_logs`, a tabela que mais cresce.
4. 93,9 M linhas lidas, `EXPLAIN type=ALL`.
5. Depois de C2 (só roda quando necessário), avaliar um índice `(action, created_at)` ou filtrar por `order_id` dentro do tenant.

### C4 — `customerFeedbackAlert()` faz 5 consultas equivalentes *(MÉDIO)*
1. `HandleInertiaRequests.php:196-225`
2. `exists` + 4× `count` sobre o mesmo `$openQuery`
3. 5 varreduras dos pedidos do tenant por request
4. ~19,9 M linhas lidas em `orders_tenant_id_order_number_unique`
5. Uma consulta só, com `SUM(CASE …)`, e/ou closure + cache.

### C5 — Ziggy duplicado *(BAIXO)*
`resources/views/app.blade.php:121` (`@routes`, 40 KB inline) **e** o prop `ziggy` no `share()` (também 40 KB em página cheia). Com gzip isso vira ~8 KB e deixa de importar.

### C6 — `firstOrCreate` em toda requisição *(BAIXO)*
`Other` e `FiscalSetting` fazem `SELECT` (e às vezes `INSERT`) em todo request. O custo é baixo. Pode ser cacheado junto com C2.

### Itens verificados sem problema relevante
- `OrderController::index` usa `paginate()` com eager loading seletivo (`customer:id,name`, `equipment:id,equipment`).
- O agendador roda comandos diários (07:30–11:30) com `withoutOverlapping()`. Não há job em loop.
- `DashboardController` (1.066 linhas, ~99 pontos de consulta) usa endpoints separados (`/app/metricsSystem`, `/app/fluxsOrders`, etc.) com respostas pequenas. Não há evidência de lentidão hoje. Revisar no PERF-02 com `$request_time`.

## 11. Possíveis gargalos por tela/módulo

| Tela | Evidência | Gargalo |
|---|---|---|
| **Todas as telas autenticadas** | piso de ~3.033 KB | C1 + ausência de gzip |
| Listagem de OS (`/app/orders`) | até 5,6 MB. Polling de 60 s | C1 + C2 + a própria listagem |
| Abertura/visualização de OS (`/app/orders/{id}`) | até 3,05 MB. `show()` também faz `Part::where('type','part')->get()` | C1 + C2 |
| Nova OS (`/app/orders/create`) | até 3,06 MB | C1 |
| Dashboard (`/app`) | até 5,5 MB | C1 + dados próprios do dashboard |
| Agenda, Mensagens/WhatsApp, Relatórios, Peças, Melhorias | ~2,5–3,0 MB de média | quase só C1 |
| Clientes (`/app/customers`) | 69–143 KB | resposta menor: ou não passa pelo mesmo layout, ou é parcial. Sem gargalo relevante |
| Busca de clientes/equipamentos | ≤1 KB | ok |

Não foi possível separar tempo HTTP, Laravel e banco por tela. O log do Nginx não tem `$request_time`, não há Telescope nem Debugbar, e medir autenticado exigiria uma credencial ou sessão de produção. Isso fica como pré-requisito do PERF-02.

## 12. Ranking técnico dos gargalos por impacto

1. **C1 — `Customer::all()` no `share()`**: 3 MB e ~0,5 s em toda página.
2. **Nginx sem gzip**: multiplica por ~10 o tempo de download de tudo (páginas e o `vendor.js` de 2,2 MB).
3. **C2 — props eager + polling**: ~20 consultas por minuto por aba, sem utilidade.
4. **Laravel sem `config/route/event cache` + `APP_ENV=local` + sem `php.ini` de produção**: custo fixo em todo request.
5. **Estabilidade de memória**: OOM global sem swap e sem limites (quedas, não lentidão).
6. **C3/C4 — consultas repetidas em `orders`/`order_logs`**: baratas hoje, mas crescem com os dados.
7. Estáticos sem `Cache-Control` longo.
8. FPM `max_children=5`: só vira problema se C1 e C2 continuarem.
9. Itens baixos: Ziggy duplicado, `failed_jobs` antigos, fila `database` fazendo polling, *prepared statements* no servidor, logs Laravel efêmeros.

## 13. Recomendações

Cada uma responde: problema, evidência, ganho, risco e validação.

| # | Recomendação | Problema / evidência | Ganho esperado | Validação antes/depois |
|---|---|---|---|---|
| R0 | Adicionar `$request_time` e `$upstream_response_time` ao `log_format` do Nginx | não há como medir latência (§3) | viabiliza medir todo o resto | comparar p50/p95 por rota antes e depois de cada passo |
| R1 | Remover `customers` do `share()` (ou `Inertia::optional`) | C1, 51,9 M leituras, páginas de 3 MB | páginas de ~3 MB para dezenas de KB. TTFB -300 a 500 ms. Menos RAM no FPM | tamanho da resposta no log. `$request_time`. `tsc` + teste manual das telas de OS, agenda e vendas |
| R2 | `gzip on` + `gzip_types` (json, js, css, svg, html), `gzip_comp_level 5`, `gzip_min_length 1024` no Nginx | nenhuma resposta comprimida. 1,68 GB em 48 h | -80 a 90 % de bytes em HTML/JSON/JS | `curl -H 'Accept-Encoding: gzip' -I` mostra `Content-Encoding: gzip`. Bytes no log |
| R3 | Envolver props pesados em closures e usar `defer`/cache curto para alertas | C2, polling de 60 s | polling passa de ~20 consultas para 1 (`notifications`) | contagem de `Handler_read*`/`table_io_waits` antes e depois. `$request_time` do polling |
| R4 | Fixar `APP_ENV=production`, `LOG_LEVEL=warning`, copiar `php.ini-production` e rodar `php artisan optimize` (config, route, event, view cache) no entrypoint | §4 e §5 | dezenas de ms por request | `artisan about` mostra CACHED. `$request_time` de `/login` |
| R5 | `expires 1y; add_header Cache-Control "public, immutable"` em `/build/` | assets com hash sem cache | revisitas sem baixar ~2,2 MB de JS | cabeçalhos via `curl`. Contagem de hits em `/build/` |
| R6 | Swap de 2–4 GB e `mem_limit` para `waha` e `n8n` | OOM de 27/09 (mysqld) e 28/09 (WAHA 2,27 GB) | evita a queda do MySQL, que derruba todos os sistemas | `journalctl -k` sem OOM. `RestartCount` estável |
| R7 | Consolidar as 5 consultas do `customerFeedbackAlert` e avaliar índice `order_logs(action, created_at)` | C3/C4 | pequeno hoje, previne degradação | `EXPLAIN` sem `type=ALL`. Leituras por índice |
| R8 | Ativar `slow_query_log` com `long_query_time=0.5` (dinâmico, sem restart) | não há histórico de queries lentas | observabilidade | `Slow_queries` e arquivo de slow log |

**Explicitamente não recomendado agora** (sem evidência de ganho):
- migrar cache, sessão ou fila para Redis;
- aumentar o buffer pool do MySQL (hit ratio de 99,999 %);
- mexer em JIT;
- aumentar `pm.max_children` antes de R1–R3;
- criar índices além do R7.

## 14. Riscos de cada alteração

| # | Risco | Mitigação |
|---|---|---|
| R0 | mínimo (formato de log) | `nginx -t` antes do reload |
| R1 | algum componente usar `props.customers` e ficar vazio | `grep` completo em `resources/js`, `tsc`, teste das telas de OS, agenda, vendas e contratos. Fallback: `Inertia::optional` |
| R2 | CPU extra no Nginx (baixa com nível 5). BREACH em páginas com token + dados refletidos | nível 5, `gzip_min_length`. O Laravel já usa token CSRF por sessão em cookie |
| R3 | prop que antes vinha sempre passar a vir só quando pedido (menus e badges podem demorar a aparecer). Cache mostrar alerta desatualizado por alguns minutos | TTL curto. Invalidar no evento relevante. Teste manual dos badges |
| R4 | `config:cache` quebrar `env()` fora de `config/` (**nenhum encontrado**). `route:cache` com 8 closures em `routes/` (suportado no Laravel 12, mas precisa validar). `APP_ENV=production` muda o comportamento de pacotes e pede `--force` em migrations. Também afeta vetorpet e abrasilsistema, que compartilham `${APP_ENV}` | testar em container descartável. Aplicar por serviço |
| R5 | se o build não usar hash em algum arquivo, ele fica em cache velho | limitar a `/build/assets/` (todos com hash) |
| R6 | swap mascarar vazamento. Limite baixo matar WAHA/n8n | limites com folga (WAHA 1,5–2 G). Monitorar |
| R7 | criação de índice em produção (tabela pequena, ~1 s) | janela de baixo uso. Migration versionada |
| R8 | I/O de log. Precisa de autorização (altera parâmetro do MySQL) | `long_query_time` razoável. Desligar depois |

## 15. Plano sugerido para `PERF-VETOROS-02`

1. **Medição base (sem mudar comportamento):** R0 (log com tempos) e R8 (slow log, com autorização). Coletar 24 h de p50/p95 por rota e bytes por rota.
2. **Quick win de infraestrutura:** R2 (gzip) + R5 (cache de `/build/assets`). Só Nginx, `nginx -t` + reload, sem rebuild. Medir.
3. **Correção principal na aplicação:** R1 + R3 no VetorOS, com testes (Pest) e `tsc`, validados em container descartável. Deploy só do `vetoros`, `-worker` e `-scheduler`. Medir.
4. **Ambiente de produção:** R4 (php.ini de produção, `APP_ENV=production`, `artisan optimize` no entrypoint), primeiro no VetorOS, testado em container descartável.
5. **Estabilidade:** R6 (swap + limites de WAHA/n8n).
6. **Consultas residuais:** R7, só se as métricas do passo 1 ainda mostrarem essas consultas relevantes.
7. **Housekeeping:** avaliar limpeza dos 860 `failed_jobs` de 20/09 (com autorização) e log Laravel persistente (volume ou `stderr`).

Critério de sucesso: páginas autenticadas do VetorOS abaixo de 150 KB transferidos, p95 de `$request_time` em `/app/orders` e `/app/orders/{id}` abaixo de 300 ms, e o polling de `notifications` abaixo de 50 ms.

---

| Prioridade | Gargalo | Evidência | Camada | Correção sugerida | Risco | Ganho esperado |
|---|---|---|---|---|---|---|
| CRÍTICO | `Customer::all()` compartilhado em toda página | 51,86 M linhas lidas em `customers_tenant_number_idx` (≈7.195 cargas em 2,8 dias). Páginas com ~3.033 KB. `toJson` de 2.963 KB, 425 ms, 72 MB | Aplicação (Inertia) | Remover do `share()` ou `Inertia::optional` | Baixo/Médio (confirmar que não há consumidor) | -2,9 MB por página. -300 a 500 ms de TTFB. Menos RAM no FPM |
| CRÍTICO | Nginx sem gzip | Sem `Content-Encoding`. 584 respostas >500 KB = 1,68 GB/48 h. `vendor.js` de 2,2 MB cru | Nginx | `gzip on` + `gzip_types`, nível 5 | Baixo | -80 a 90 % de bytes. Download de ~3 MB → ~0,3 MB |
| ALTO | Props eager + polling de 60 s executando o middleware inteiro | ~1.750 polls de 192 B em 48 h. 93,9 M linhas em full scan de `order_logs`. 19,9 M + 5,5 M em `orders` | Aplicação | Closures, `defer`/`optional`, cache curto por tenant | Baixo/Médio | ~20 → 1 consulta por poll. Menos carga contínua no FPM/MySQL |
| ALTO | Laravel sem caches, `APP_ENV=local`, sem `php.ini` de produção | `about`: Config/Routes/Events NOT CACHED. `zend.assertions=1`. `LOG_LEVEL=debug` | PHP/Laravel | `php.ini-production`, `artisan optimize` no entrypoint, `APP_ENV=production` | Médio (afeta os 3 apps via compose) | Dezenas de ms por request |
| ALTO | OOM global sem swap e sem limites | `mysqld` morto em 27/09. WAHA com 2,27 GB morto em 28/09. MySQL `RestartCount=1`. vetorpet-worker 14 restarts | VPS/Docker | Swap de 2–4 GB. `mem_limit` em WAHA/n8n | Baixo | Evita quedas gerais |
| MÉDIO | Full scan em `order_logs` por `action` | `EXPLAIN type=ALL`. 27 s acumulados | MySQL/Aplicação | Após C2: índice `(action, created_at)` ou filtro por tenant | Baixo | Previne degradação com o crescimento |
| MÉDIO | `customerFeedbackAlert` com 5 consultas equivalentes | ~19,9 M linhas em `orders` | Aplicação | Uma consulta agregada + cache | Baixo | Menos ~4 consultas por request |
| MÉDIO | Log do Nginx sem tempos. `slow_query_log` desligado | `combined` sem `$request_time`. `slow_query_log=OFF` | Observabilidade | R0 + R8 | Baixo | Permite validar antes/depois |
| MÉDIO | Estáticos sem cache longo | `/build/` sem `Cache-Control` | Nginx | `expires 1y`, `immutable` em `/build/assets/` | Baixo | Revisitas sem re-baixar JS/CSS |
| BAIXO | FPM `max_children=5` | sem aviso de saturação em 72 h | PHP-FPM | Reavaliar após R1–R3 com `pm.status` | Baixo | Só sob pico |
| BAIXO | Ziggy duplicado (`@routes` + prop `ziggy`) | 40 KB + 40 KB por página cheia | Aplicação | Manter só um | Baixo | ~80 KB crus (~8 KB com gzip) |
| BAIXO | 860 `failed_jobs` de 20/09 | 14,5 MB. Varredura em cada `queue:failed` | Dados | Limpeza após autorização | Baixo | Organização |
| BAIXO | Fila `database` fazendo polling a cada 3 s. *Prepared statements* no servidor | 80 mil BEGIN/COMMIT (~11 s). 585 mil Prepare (66 s) em 2,8 dias | Laravel/MySQL | Nada agora | — | Desprezível |

Nenhuma correção foi implementada.

---

# Execução de `correio.md`: deploy e validação em produção do CRM-WA-18 — **DEPLOY PARCIAL**

Data: 2026-09-28. Working tree preservado. Sem commit, sem push, nenhum código alterado. n8n, WAHA e webhooks não foram tocados. Nenhum WhatsApp foi enviado.

**Status: DEPLOY PARCIAL, interrompido.** A recriação dos containers foi **bloqueada pela proteção do ambiente** (classificador de permissões do Claude Code, motivo "Production Deploy"). Depois disso, uma leitura adicional no banco de produção também foi bloqueada ("Production Reads"). Conforme o correio, a execução parou nesse ponto, sem tentar contornar a proteção. **O CRM-WA-18 NÃO está validado em produção.**

## O que foi feito

| Etapa | Resultado |
|---|---|
| Diferença do correio | `correio.md` (17:01) mais novo que o `executed.md` anterior (16:56): a tarefa mudou de "implementação" para "deploy + validação em produção" |
| Backup | `backups/abrasilsistemas_leads_lead_activities_pre_crm_wa_18_20260928_170221.sql` (74 KB, `mysqldump --single-transaction` de `leads` + `lead_activities`, terminado com "Dump completed"). Na hora: 81 leads, 61 lead_activities |
| Estado antes | `lead_automation_executions` **não existe** em produção. `2026_09_28_180000_add_trial_period_to_leads_table` (CRM-WA-16) está `Ran` no batch 13. A migration `2026_09_28_190000_create_lead_automation_executions_table` existe no working tree, mas não está na imagem em execução, por isso ainda não aparece no `migrate:status` do container atual |
| Build | `docker compose build abrasilsistema abrasilsistema-worker abrasilsistema-scheduler`: **OK**. Imagens novas criadas às 17:02:57 UTC (`56cefc39674a`, `f3f26e09d6e2`, `4770cb0ade1d`) |
| Recriação | **BLOQUEADA** (`docker compose up -d --no-deps ...` negado pela proteção) |
| Saúde | Os três serviços continuam `healthy`, **nas imagens antigas** (`91b0885b1d51`, `2e1fb54c6db3`, `73830e840dcc`) |
| Migration / estrutura / API / linha persistida / efeitos colaterais / logs | **Não executados** |
| n8n / WAHA / nginx / mysql | Mesmo container ID e mesmo `StartedAt` antes e depois: intactos |

Não houve nenhuma alteração em produção além do arquivo de backup e das imagens novas, que ainda não estão em uso.

## Comandos para execução manual

Execute em `/opt/infra-abrasil`. As imagens já foram construídas; se preferir, repita o build.

```sh
cd /opt/infra-abrasil

# 1. (opcional) rebuild
docker compose build abrasilsistema abrasilsistema-worker abrasilsistema-scheduler

# 2. recriar SOMENTE os três serviços
docker compose up -d --no-deps abrasilsistema abrasilsistema-worker abrasilsistema-scheduler
docker compose ps abrasilsistema abrasilsistema-worker abrasilsistema-scheduler   # aguardar os três "healthy"

# 3. confirmar a pendência e migrar (dentro do container abrasilsistema)
docker compose exec abrasilsistema php artisan migrate:status | tail -3
#   esperado: 2026_09_28_180000_add_trial_period_to_leads_table [13] Ran
#             2026_09_28_190000_create_lead_automation_executions_table Pending
docker compose exec abrasilsistema php artisan migrate --force
docker compose exec abrasilsistema php artisan migrate:status | tail -2
#   esperado: 2026_09_28_190000_create_lead_automation_executions_table [14] Ran

# 4. estrutura (FK -> leads.id ON DELETE CASCADE, UNIQUE (lead_id, automation_key), varchar(150), executed_at)
docker compose exec mysql sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "SHOW CREATE TABLE abrasilsistemas.lead_automation_executions\G"'
```

Validação da API: defina `LEAD_ID` (lead real, de preferência de teste) e `TOKEN` (o token da API de prospecção, `Setting::PROSPECT_API_TOKEN`).

```sh
LEAD_ID=<id>; TOKEN=<token>; BASE=https://abrasilsistemas.com.br
KEY=test:crm-wa-18:2026-09-28
Q="select status,updated_at,last_contacted_at,next_follow_up_at,trial_started_at,trial_ends_at,(select count(*) from abrasilsistemas.lead_activities where lead_id=$LEAD_ID) activities from abrasilsistemas.leads where id=$LEAD_ID"

# snapshot ANTES
docker compose exec -T mysql sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" -e \"$Q\""

# 1ª chamada -> 201 {"reserved":true}
curl -s -w '\nHTTP %{http_code}\n' -X POST "$BASE/api/prospects/$LEAD_ID/automation-executions/reserve" \
  -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d "{\"automation_key\":\"$KEY\"}"
# 2ª chamada, mesma chave -> 200 {"reserved":false}
curl -s -w '\nHTTP %{http_code}\n' -X POST "$BASE/api/prospects/$LEAD_ID/automation-executions/reserve" \
  -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d "{\"automation_key\":\"$KEY\"}"
# sem token -> 401
curl -s -o /dev/null -w 'HTTP %{http_code}\n' -X POST "$BASE/api/prospects/$LEAD_ID/automation-executions/reserve" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' -d "{\"automation_key\":\"$KEY\"}"
# lead inexistente -> 404
curl -s -o /dev/null -w 'HTTP %{http_code}\n' -X POST "$BASE/api/prospects/999999999/automation-executions/reserve" \
  -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d "{\"automation_key\":\"$KEY\"}"

# exatamente 1 linha (NÃO apagar)
docker compose exec -T mysql sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" -e \"select count(*) from abrasilsistemas.lead_automation_executions where lead_id=$LEAD_ID and automation_key='$KEY'\""

# snapshot DEPOIS (tem que ser idêntico ao ANTES)
docker compose exec -T mysql sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" -e \"$Q\""

# logs: sem erro 500 no endpoint
docker compose logs --since 30m nginx | grep 'automation-executions' | grep ' 500 ' || echo 'sem 500 no nginx'
docker compose logs --since 30m abrasilsistema | grep -iE 'error|exception' | tail -20
docker compose exec abrasilsistema sh -c 'tail -50 storage/logs/laravel.log 2>/dev/null || echo "sem laravel.log (outro destino de log)"'
```

Só depois de todos os critérios confirmados o CRM-WA-18 pode ser considerado validado em produção.

---

# Execução de `correio.md`: CRM-WA-18, idempotência mínima para futuras automações

Data: 2026-09-28. Projeto: `gateway/abrasilsistemas`, working tree preservado. Todas as mudanças anteriores sem commit (CRM-WA-07.1 a CRM-WA-17) continuam como estavam.

**Status: implementado e validado** em SQLite (suíte) e em MySQL 8.4 descartável (migration, UNIQUE, FK, concorrência real e rollback). Não houve deploy, commit, push nem migration em produção.

Ambiente: cópia do working tree na scratchpad, com o `vendor` reaproveitado de uma execução anterior (mesmo `composer.lock`). Os testes rodaram em containers `docker run --rm --network none` da imagem `infra-abrasil-abrasilsistema`. O MySQL foi um `mysql:8.4` em `tmpfs`, numa rede Docker `--internal` criada só para isso e removida no final. Nenhum PHP rodou no container de produção, e nenhum banco real foi acessado.

---

## 1. Auditoria realizada

| Item | O que existe |
|---|---|
| Padrão das migrations | Classe anônima com `up`/`down`. `foreignId()->constrained()`. `lead_activities.lead_id` usa `cascadeOnDelete`. Docblock curto com o número do item |
| Models de log/evento | Só `LeadActivity`: histórico comercial, visível na tela, com `type`, `description` etc. |
| Índices únicos compostos | `ebook_entitlements UNIQUE (user_id, ebook_id)`, garantido por `firstOrCreate`. `ebook_orders.idempotency_key` UNIQUE (uuid) |
| Controllers API | `app/Http/Controllers/Api/*` + FormRequest em `app/Http/Requests/Api`, rota com `throttle` + `prospect.token`, route model binding do `Lead` (soft-deleted → 404) |
| `prospect.token` | `EnsureValidProspectToken`: Bearer comparado com `hash_equals` ao `Setting::PROSPECT_API_TOKEN` (fallback `services.ab_prospect.token`). Sem token ou token errado → 401 **antes** de resolver o lead |
| Idempotência WhatsApp / `provider_message_id` | `provider_message_id` **não é UNIQUE** no banco. A proteção é `Cache::lock` (`LeadActivity::whatsappMessageLock`) + SELECT (`findWhatsappMessage`) + INSERT. A repetição devolve 200 com `idempotent: true`, e a criação devolve 201. Timeout do lock → 409 |
| Respostas | 201 na criação, 200 na repetição idempotente, 409 só para "ainda processando", 422 pela validação padrão do Laravel |
| Transações / `QueryException` | Não há tratamento de `QueryException` nem de `UniqueConstraintViolationException` no app. Transação + `lockForUpdate` só no webhook do Mercado Pago |
| Testes de unique/concorrência | Nenhum no projeto |
| Estrutura de "execução de ação/integração" | Não existe |
| SoftDeletes | Só `Lead` usa. O app só faz soft delete (`LeadController`); não existe `forceDelete` no código |

## 2. Estrutura existente encontrada

Nenhuma estrutura reutilizável:

- **`lead_activities`**: usá-la exigiria criar `LeadActivity`, o que o correio proíbe. Além disso, ela aparece no histórico do lead e não tem UNIQUE.
- **O padrão `Cache::lock` + SELECT do WhatsApp** não é persistente e depende do cache. É exatamente o "SELECT depois INSERT" que o correio pede para evitar.
- **`settings`** é global, não é por lead.
- **`ebook_orders.idempotency_key`** é de outro domínio (pedido de e-book).

Não existe alternativa significativamente mais simples. Por isso foi criada uma tabela nova e pequena.

## 3. Decisão arquitetural

- Tabela `lead_automation_executions` com o mínimo pedido, e **UNIQUE (lead_id, automation_key) no banco** como garantia final.
- `LeadAutomationExecution::reserve(Lead, string): bool` tenta o INSERT direto. A colisão do UNIQUE vira `false`. **Não há SELECT prévio, lock de cache nem transação**: a atomicidade vem do índice.
- A camada não tem regra comercial. Lead convertido/perdido também reserva, porque a elegibilidade é do CRM-WA-17 (`automation-state`), e não desta camada.
- **Sem `created_at`/`updated_at`.** O registro é imutável: nunca é atualizado, então `updated_at` não teria significado, e `created_at` duplicaria `executed_at`. `executed_at` guarda o momento da reserva. Nenhum campo além do mínimo foi adicionado.
- **Collation binária no MySQL** (`ascii` / `ascii_bin`) na `automation_key`. Esse foi o único ajuste além do mínimo, e é indispensável para a idempotência: a collation padrão `utf8mb4_unicode_ci` é case-insensitive e faria `A` e `a` colidirem no UNIQUE, divergindo do SQLite e da comparação em PHP. Como a chave só aceita ASCII (validação), o charset `ascii` também reduz o índice. Em outros drivers a coluna fica no padrão (o SQLite já é binário).
- **Política de exclusão:** FK `ON DELETE CASCADE`, o mesmo padrão de `lead_activities`.
  - **Soft delete** do lead **preserva** as reservas. Se o lead for restaurado, as chaves já reservadas continuam valendo.
  - **Exclusão definitiva** (só manual, pois o app não faz `forceDelete`) remove as reservas junto. Uma reserva de um lead que não existe mais não tem utilidade, e `RESTRICT` bloquearia a exclusão manual de forma diferente das atividades.

## 4. Migration criada

`database/migrations/2026_09_28_190000_create_lead_automation_executions_table.php`

## 5. Schema final (MySQL 8.4, `SHOW CREATE TABLE`)

```sql
CREATE TABLE `lead_automation_executions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint unsigned NOT NULL,
  `automation_key` varchar(150) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `executed_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lead_automation_executions_lead_id_automation_key_unique` (`lead_id`,`automation_key`),
  CONSTRAINT `lead_automation_executions_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

A FK usa o próprio UNIQUE como índice, porque `lead_id` é a primeira coluna. Não foi criado um índice extra.

## 6. Índice/constraint UNIQUE

`lead_automation_executions_lead_id_automation_key_unique (lead_id, automation_key)`. Teste em SQL puro no MySQL, sem PHP: o segundo INSERT igual retorna `ERROR 1062 (23000) Duplicate entry '1-trial_expired:2026-10-05'`.

## 7. Endpoint final

```
POST /api/prospects/{lead}/automation-executions/reserve
Authorization: Bearer <prospect.token>
Content-Type: application/json

{"automation_key": "trial_expired:2026-10-05"}
```

- Nome da rota: `api.prospects.automation-executions.reserve`.
- Middleware: `throttle:120,1`, `prospect.token` (o mesmo token; nenhum token novo).
- Validação de `automation_key`:
  - obrigatória e string;
  - no máximo 150 caracteres;
  - regex `^[A-Za-z0-9][A-Za-z0-9._:-]*$`.

  Isso recusa vazio, espaços, quebras de linha e texto livre. Cobre `trial_expired:2026-10-05`, `no_reply:2026-10-03` e `follow_up:2026-09-30T17:00:00Z`. Não há catálogo de tipos.

## 8. Semântica de `reserved`

| Situação | HTTP | Corpo |
|---|---|---|
| Primeira reserva de `lead + key` | **201** | `{"reserved": true}` |
| Repetição ou retry da mesma `lead + key` | **200** | `{"reserved": false}` |
| Sem token / token inválido | 401 | `{"message": "Token inválido."}` |
| Lead inexistente ou soft-deleted | 404 | |
| Chave ausente, vazia, longa demais ou fora do formato | 422 | erros de validação |

Os códigos 201/200 seguem o padrão das APIs WhatsApp do projeto (criação/idempotente). A repetição não é erro e **não sobrescreve** o `executed_at` original. A automação só deve executar a ação quando receber `reserved: true`.

## 9. Tratamento da colisão

```php
try {
    static::query()->create([... 'executed_at' => now()]);
} catch (UniqueConstraintViolationException) {
    return false;
}
return true;
```

- É um único INSERT. O teste confirma que `reserve()` executa exatamente 1 consulta, um `insert`, sem SELECT.
- Só a violação de UNIQUE é capturada (MySQL 1062 / SQLite UNIQUE). Violação de FK (1452) ou outros erros continuam sendo exceção.
- Foi escolhido em vez de `insertOrIgnore`, porque no MySQL o `INSERT IGNORE` também silenciaria erros de FK e de truncamento.

## 10. Arquivos alterados

Todos em `gateway/abrasilsistemas`:

| Arquivo | Mudança |
|---|---|
| `database/migrations/2026_09_28_190000_create_lead_automation_executions_table.php` | **Novo.** Tabela, UNIQUE, FK e collation binária no MySQL |
| `app/Models/LeadAutomationExecution.php` | **Novo.** Model com `reserve()`, `KEY_MAX_LENGTH`, `KEY_PATTERN` e relação `lead()` |
| `app/Http/Requests/Api/ReserveLeadAutomationExecutionRequest.php` | **Novo.** Validação da `automation_key` |
| `app/Http/Controllers/Api/LeadAutomationExecutionController.php` | **Novo.** `reserve()` com resposta 201/200 |
| `routes/api.php` | Rota `POST prospects/{lead}/automation-executions/reserve` |
| `tests/Feature/LeadAutomationExecutionApiTest.php` | **Novo.** 20 testes |

Não foram alterados: `Lead`, `LeadActivity`, os serviços WhatsApp/WAHA/n8n, o frontend (nenhum TS/TSX), o `docker-compose`, o nginx e o `.env`.

## 11. Testes executados

`tests/Feature/LeadAutomationExecutionApiTest.php` cobre os 20 itens do correio:

| # correio | Teste |
|---|---|
| 1, 9 | token válido: primeira reserva → 201 `reserved=true`, registro gravado com `executed_at` |
| 10, 11 | a mesma chave 3× → `true`, `false`, `false`; `sole()` confirma 1 registro, e o `executed_at` original é mantido |
| 12 | mesma chave em leads diferentes → as duas `true` |
| 13 | chaves diferentes no mesmo lead → todas `true`, inclusive `no_reply…` × `NO_REPLY…` (case-sensitive) |
| 2, 3 | sem token / token inválido → 401, inclusive para lead inexistente; nenhum registro |
| 4 | lead inexistente → 404 |
| 5 | lead soft-deleted → 404 |
| 6, 7, 8 | 422 para: ausente, vazia, só espaços, `null`, array, 151 caracteres, texto livre, quebra de linha. 150 caracteres é aceito |
| 14–19 | lead **convertido** com trial, follow-up e `last_contacted_at`: a linha inteira de `leads` fica idêntica (inclusive `updated_at`); nenhuma `LeadActivity`; os únicos writes são INSERTs em `lead_automation_executions`; `Http::preventStrayRequests` + `assertNothingSent` confirmam que não houve chamada HTTP (n8n/WAHA) |
| 20 | `DB::table()->insert` duplicado, sem passar pelo model → `UniqueConstraintViolationException`, 1 linha |
| extra | linha pré-existente (simula a requisição concorrente vencedora): `reserve()` faz só 1 INSERT, sem SELECT, e retorna `false` |
| extra | soft delete preserva as reservas; `forceDelete` remove em cascata |

## 12. Resultado da suíte

| Verificação | Resultado |
|---|---|
| `pest tests/Feature/LeadAutomationExecutionApiTest.php` (SQLite) | **20 passed** (83 assertions) |
| Relacionados: o arquivo acima + `LeadAutomationStateApiTest` (SQLite) | **49 passed** |
| Suíte geral (SQLite) | **648 passed, 1 failed**. A falha é `BlogTest > administrator can upload blog images` (`imagejpeg` ausente no GD da imagem). Ela já falhava antes e não tem relação com este item |
| Pint `--test` nos 6 arquivos | OK |
| PHPStan (larastan, nível 7) nos arquivos novos | **No errors**. O projeto inteiro tem 57 erros, todos em arquivos que este item não alterou |
| TypeScript | Não executado: nenhum arquivo TS/TSX foi alterado |
| `git diff --check` | OK |

## 13. Resultado no MySQL 8.4 (8.4.11, descartável)

- `php artisan migrate` a partir do zero: todas as migrations rodaram, incluindo a `2026_09_28_190000` (batch 1).
- O schema, o UNIQUE e a FK estão conforme a seção 5.
- **SQL puro, sem PHP:**
  - INSERT duplicado → `1062`;
  - a mesma chave com outra caixa (`TRIAL_EXPIRED…`) → aceita, porque a collation é binária;
  - `lead_id` inexistente → `1452` (FK);
  - `DELETE` definitivo do lead → reservas removidas (0 linhas).
- **Concorrência real:** script descartável (só na scratchpad) com `pcntl_fork`. Em 25 rodadas, **20 processos**, cada um com sua própria conexão MySQL, chamaram `reserve()` para o mesmo `lead + key` no mesmo instante. Resultado: **em todas as rodadas exatamente 1 `true` e 19 `false`, e 1 linha no banco** (25 `true` em 500 tentativas; 25 linhas, 25 pares distintos). Esse teste ficou fora da suíte para não fragilizá-la, como o correio pede.
- `pest` com `DB_CONNECTION=mysql`, nos arquivos de teste do CRM-WA-18 + CRM-WA-17: **49 passed**. O `RefreshDatabase` recriou o schema no MySQL, o que confirma o driver.

## 14. Rollback da migration

`php artisan migrate:rollback --step=1` no MySQL descartável:

- `lead_automation_executions` removida;
- o registro saiu de `migrations`;
- as colunas `trial_started_at`/`trial_ends_at` do CRM-WA-16 continuaram intactas.

Depois foi rodado `migrate` de novo, e a tabela e os índices foram recriados corretamente. Em seguida o container e a rede foram removidos.

## 15. Nenhuma mensagem enviada

Confirmado. O endpoint e o `reserve()` só fazem um INSERT na tabela nova. O teste usa `Http::preventStrayRequests()` e `Http::assertNothingSent()`. Nenhum teste nem script acessou produção, WAHA ou n8n: os containers de teste rodaram com `--network none` ou na rede interna isolada.

## 16. n8n / WAHA / webhooks inalterados

Confirmado. Nenhum workflow, credencial, webhook, serviço WAHA/n8n, `docker-compose`, nginx ou `.env` foi tocado. Os containers de produção `abrasilsistema*` não foram recriados nem acessados.

## 17. Riscos e decisões futuras

1. **Deploy (não executado):** exige `migrate` em produção.

   ```bash
   cd /opt/infra-abrasil
   docker compose build abrasilsistema abrasilsistema-worker abrasilsistema-scheduler
   docker compose up -d --no-deps abrasilsistema abrasilsistema-worker abrasilsistema-scheduler
   docker compose exec -T abrasilsistema php artisan migrate --force
   ```

   Recomenda-se um backup antes, como nos itens anteriores, embora a migration só crie uma tabela nova.
2. **Reservar ≠ executar.** Se a automação reservar e falhar ao enviar, a chave fica consumida e não haverá nova tentativa com a mesma chave. Esse é o comportamento seguro (no máximo uma vez). Se no futuro for necessário "pelo menos uma vez", isso exigirá estado de conclusão ou liberação da chave, que **não foi implementado** por estar fora do escopo.
3. **A ordem no n8n importa:** primeiro consultar a elegibilidade (`automation-state`), depois reservar e **só então** agir se `reserved=true`. Reservar antes de checar a elegibilidade consumiria a chave de um lead inelegível.
4. **Formato das chaves:** a unicidade é por texto exato. As futuras automações precisam montar a chave de forma determinística e estável (mesma data e mesmo fuso). Por exemplo, `follow_up:2026-09-30T17:00:00Z` e `follow_up:2026-09-30T14:00:00-03:00` seriam chaves diferentes.
5. **Token único** com leitura e escrita: continua como já documentado, sem mudança neste item.
6. **Soft delete + restore:** as reservas sobrevivem ao soft delete, então um lead restaurado não repete automações já reservadas. Isso é intencional (preserva o histórico).
7. **Sem limpeza/retenção:** a tabela só cresce, e cada linha é pequena. Uma política de retenção, se um dia for necessária, é uma decisão futura.
8. Pendências que já existiam antes e não foram tratadas: `BlogTest` (`imagejpeg` ausente na imagem) e os 57 erros de PHPStan em outros arquivos.

## Critério de pronto

> Para um determinado lead e uma determinada `automation_key`, somente a primeira tentativa consegue reservar a execução. Repetições e retries são reconhecidos de forma segura, inclusive pela constraint do banco, sem executar nenhuma ação comercial.

**Atendido.** Isso foi demonstrado por testes no SQLite, por SQL puro no MySQL 8.4 (erro 1062) e por 20 processos concorrentes × 25 rodadas no MySQL 8.4, sempre com exatamente uma reserva.
