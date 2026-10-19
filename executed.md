# VETORLEADS-PROSPECT-08 — consolidação técnica e preparação da homologação comercial

Execução concluída em **19/10/2026 UTC**. O relatório anterior foi integralmente substituído.

## Comparação do correio

- Anterior, PROSPECT-07: `c19fa57a8fe88ab79638870db61467e9a73283a15604b37a34ef22d02f149a41`.
- Executado, PROSPECT-08: `00c7f00a20abb3be555f249a18bc801834e378ab00327a62d4e2d16f9b3c19af`.

**Resultado:** trabalho anterior preservado, alterações organizadas em commits locais, testes completos aprovados e roteiro de implantação/homologação preparado. **Sem push, deploy, migrations operacionais, reinicialização de serviços operacionais, requalificação em massa ou escrita de registros comerciais reais. A homologação operacional real permanece pendente.**

## Correções locais desta etapa

1. **Recaptura conserva a qualificação:** a extensão atualiza lacunas e o histórico de captura, mas não recalcula mais o score existente sem prévia/confirmação. A primeira captura continua recebendo sua qualificação inicial. Um teste demonstra a preservação inclusive de score v2 após recebimento de dados adicionais.
2. **Selo comercial coerente com v3:** `Qualificado` agora exige prioridade comercial alta, com aderência e contato suficientes. Tier numérico alto isolado não torna um produto incorreto ou segmento excluído favorável. Casos com contato forte e produto/segmento incompatível foram testados. Pesos e critérios v3 preservados.
3. **Verificação de site/CNPJ conserva o score:** a ação opcional continua verificando/preenchendo os campos permitidos, mas a atualização da qualificação existente fica para uma prévia confirmada. Mensagem de sucesso orienta esse passo; um teste verifica a preservação. Consultas externas foram simuladas na validação.
4. **Contrato da extensão verificado:** teste adicional do transporte valida bearer token, sucesso, erros HTTP 401/422/500 e falha de rede sem chamar serviço real.

A prévia/confirmação individual continua vinculada a tenant, usuário, prospect, produto e hash dos dados/critérios, com expiração e rejeição de contexto alterado. Nenhum campo ausente foi inventado; telefone não foi tratado como WhatsApp ativo. Não foram adicionados menus, indicadores ou arquitetura paralelos nesta etapa.

## Repositórios e commits

As referências remotas foram conferidas com `git ls-remote`, sem fetch/pull/push. Os onze commits anteriores do VetorLeads foram preservados: senha, eventos n8n, WAHA por tenant, UX-01/02/03, identificação WebChat, menu da conta, landing e GD JPEG/WebP. Inventário completo: [onze-commits-anteriores.txt](docs/vetorleads-prospect-08/onze-commits-anteriores.txt).

| Repositório | Estado consolidado local | Relação com remoto conferido |
| --- | --- | --- |
| VetorLeads, `main` | HEAD `adcc6e4`, árvore limpa | **14 commits à frente / 0 atrás** de `origin/main` `a274619` |
| VetorOS, `deploy/vetor-intel-20261008` | HEAD `24716ef8`, árvore limpa, sem alteração de código nesta etapa | **1 commit à frente / 0 atrás** do upstream `origin/main` `438178b0` |
| Infra, `main` | Configuração e ponteiros consolidados; relatório/evidências em commit próprio | **3 commits locais desta etapa**, sem publicação; remoto continua `a4b8bd4` |

Commits novos do VetorLeads:

- `6c614bb` — landing e preferências de privacidade, incluindo retirada dos assets antigos não referenciados.
- `a9ba459` — captura, critérios v3, prévia/auditoria, conversão, UX comercial e testes consolidados.
- `adcc6e4` — preservação da qualificação após verificação até confirmação da prévia.

Na infraestrutura: `7a0c9f8` consolida as variáveis já existentes de WebChat/n8n e os ponteiros; `8aaa1f3` registra a proteção final do VetorLeads. O terceiro commit contém este relatório, `correio.md` e as evidências/documentação preservadas das etapas anteriores. Seu identificador consta no histórico Git, evitando autorreferência no próprio relatório.

Nenhum commit anterior foi reescrito ou descartado. VetorOS e demais produtos foram preservados. Os snapshots da pasta de evidências identificam expressamente quando foram gerados antes do commit final de documentação.

## Captura e fluxo efetivamente auditados

A extensão envia o bearer token do tenant; o servidor resolve somente token válido/não revogado/não expirado e tenant ativo, ignorando tenant forjado no payload. A captura usa as tabelas existentes, mantém origem/lote/identidade Maps e não cria lead automaticamente. Recaptura preenche lacunas conservadoramente; não altera oposição, seleção ou vínculo CRM.

Cobertos: nome público e atividade, telefones/celular/WhatsApp informado ou provável/possível, website, endereço/cidade/UF fornecidos, origem e lote, identidade Place ID/FTID, deduplicação, recaptura, validação e falhas de transporte. A API aceita razão social/nome fantasia opcionais, mas a extensão Maps não garante razão social cadastral.

Jornada demonstrada em Chromium e testes funcionais:

**Captura autenticada → lista de prospects → produto/segmentos → prévia → confirmação → seleção → conversão/vínculo CRM → dashboard.**

- Captura permanece em `/prospecting`; administração da extensão em `/settings/integrations/ab-prospect`.
- `/prospecting/prospects`: filtros por origem, contato, produto, prioridade, situação, localização e lote; detalhes explicáveis.
- `/prospect-products`: critérios comerciais por produto/tenant, sem cadastro paralelo.
- Conversão explícita e idempotente: não duplica lead ou nota; vínculo a lead existente conserva seus dados e registra origem/produto/score/versão.
- `/leads` e `/dashboard`: vínculo e histórico verificáveis; contagens vêm do banco e distinguem inclusão no CRM de venda ganha.
- Captura, prospects, produtos, CRM e dashboard revisados em desktop; lista e prévia também em 390/320 px. Tabela com rolagem interna no celular, sem transbordamento horizontal do documento nem erros JavaScript.

Detalhes: [auditoria-captura.md](docs/vetorleads-prospect-08/auditoria-captura.md). Evidências: [critérios](docs/vetorleads-prospect-08/interface/1440-commercial-criteria.png), [lista celular](docs/vetorleads-prospect-08/interface/320-leads.png), [prévia celular](docs/vetorleads-prospect-08/interface/390-preview.png), [CRM](docs/vetorleads-prospect-08/interface/1440-crm.png), [dashboard sintético](docs/vetorleads-prospect-08/interface/1440-dashboard.png).

## Testes executados

Primeiro foi executada a suíte completa isolada sem alterações novas: **1.268 testes / 13.460 assertions**. Após as correções:

| Validação | Resultado final |
| --- | --- |
| VetorLeads, regressão completa PHP 8.4/SQLite sintético | **1.270 testes aprovados / 13.475 assertions** |
| PHPStan | Sem erros |
| Pint | Aprovado, 321 arquivos |
| Frontend | ESLint, Prettier, TypeScript e build aprovados |
| Consentimento da landing, JavaScript | 3 testes aprovados |
| Extensão, parsers/recaptura/contrato/transporte | 18 testes aprovados |
| VetorOS, proxy/disponibilidade WebChat | 5 testes aprovados / 17 assertions |
| Navegador Chromium | Jornada completa aprovada, 1440/390/320 px |
| Compose | Configuração validada, sem aplicação |
| Git | Verificação de whitespace aprovada |

Os testes funcionais cobrem multitenancy, permissões, token, oposição, prévia e confirmação inválida/expirada/desatualizada, deduplicação e histórico. O container PHP de testes não tinha rede; integrações externas foram simuladas. Build e screenshots usam ambiente sintético separado. Logs e resultados: [docs/vetorleads-prospect-08/README.md](docs/vetorleads-prospect-08/README.md).

## Runtime, migrations e conflitos identificados

Leitura operacional por PDO, em transação **READ ONLY** com snapshot consistente, sem bootstrap do Laravel operacional: MySQL **8.4.11**, schema `vetorleads`, PHP **8.4.26**, **46 migrations aplicadas**. Consultas agregadas observaram **61 prospects, 0 prospects vinculados ao CRM, 0 leads provenientes da prospecção e 0 leads ganhos**. Esses dados não foram confundidos com os números das telas sintéticas.

Duas migrations locais pendentes, nenhuma aplicada em produção:

| Migration | Dependências e risco |
| --- | --- |
| `2026_10_18_210000_add_identification_to_web_conversations` | Depende de `web_conversations`; adiciona `subject`, `identified_at`, `whatsapp_consent_at`. As três colunas estão ausentes. **O controller de identificação WebChat no runtime tem o mesmo hash do código local**, portanto existe incompatibilidade de schema no caminho de identificação. Esse caminho não foi invocado em produção. |
| `2026_10_19_000000_add_commercial_criteria_to_prospect_products` | Depende de `prospect_products`; adiciona segmentos desejados/excluídos e preferência de site com default `any`. Campos ausentes em produção. O código comercial local difere do runtime; publicar o cadastro novo antes da migration faria gravações falharem. |

As duas são aditivas e independentes entre si, mas não se pode executar todas as pendências sem revisão. Passaram nas migrations dos testes SQLite; **DDL, locks e reversão em MySQL ainda precisam de ensaio em clone isolado**. O healthcheck atual de PHP-FPM não verifica essa compatibilidade de schema.

Os 21 serviços Compose estavam running/healthy na leitura. Nenhum serviço operacional foi reiniciado, reconstruído ou alterado. O Dockerfile já contém PHP 8.4/GD JPEG-WebP; o entrypoint copia assets para volume compartilhado. O `public/build` histórico versionado não deve ser publicado como se fosse o build recém-validado; a imagem futura precisa recompilar as fontes.

## Publicação e homologação real: pendências e roteiro

Roteiro detalhado, com critérios de interrupção e reversão: [publicacao-e-homologacao.md](docs/vetorleads-prospect-08/publicacao-e-homologacao.md).

Antes de publicar: revisar commits/referências, aprovar janela e backup com restauração testada, ensaiar as duas migrations no MySQL isolado, reconstruir imagem/assets, aprovar mudanças delimitadas e colocar o schema compatível antes dos consumidores. Coordenar VetorLeads/app/worker/scheduler e, em etapa própria, o proxy/widget VetorOS. Preservar versões anteriores e assets para reversão; não executar rollback destrutivo de colunas automaticamente.

O teste real proposto limita-se a **até três empresas e um tenant**, com empresas/URLs, operador, produto e operações expressamente autorizados antes de qualquer escrita. Inclui captura/recaptura, comparação de produtos na prévia, uma confirmação, seleção/conversão, histórico e métricas; não inclui mensagens ou contato comercial. Verificar automações antes de criar leads; não modificar configurações operacionais implicitamente. Não foram escolhidas empresas reais nesta execução.

VetorOS depende da API WebChat do VetorLeads; incompatibilidade ou indisponibilidade pode afetar o widget. MySQL, WAHA, Redis, n8n, nginx e outros produtos compartilham infraestrutura: não reiniciá-los como parte genérica de uma publicação comercial. A leitura de saúde não substitui homologação funcional. A segmentação continua lexical/explicável; compatibilidade não comprova intenção, telefone não comprova WhatsApp ativo e ausência de site não é verificação universal.

**Conclusão:** consolidação técnica concluída sem regressões conhecidas nos testes executados. Implantação, ensaio MySQL e homologação operacional com amostra real continuam pendentes das autorizações específicas descritas no roteiro.
