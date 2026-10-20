# Execução — VETORLEADS-PROSPECT

Implementação concluída localmente em 20/10/2026, sem commit, push nem publicação em produção. Há um incidente operacional pendente de confirmação (ver **Incidente**).

## Comparação do correio

O `correio.md` da raiz mudou de **VETORLEADS-QUALIFY-01** para **VETORLEADS-PROSPECT**. SHA-256 atual: `563517711b590cc99cffd0cd81ddc7b68c8fd012ae51524e123a1b42ffc1b6f6`. A instrução atual está preservada em [correio.md](docs/vetorleads-prospect/correio.md). O relatório anterior foi arquivado integralmente em [previous-executed.md](docs/vetorleads-prospect/previous-executed.md), SHA-256 `7bb9fedf8d8fddc88d91afb338a8da33adf24f4853db307e4edfcfcdd13231ad`.

Base do código: VetorLeads `adcc6e4` com as alterações não commitadas de VALIDATE-01 e QUALIFY-01 preservadas.

## Implementação

- **Motor único** (`ProspectAnalysis`): normalização → enriquecimento de site → validação empresarial → qualificação → detecção de duplicados. Reaproveita `ProspectNormalizer`, `ProspectEnricher`, `WebsiteProbe`, `BrasilApiCnpjLookup`, `CompanyValidator`, `CompanyIdentity` e `ProspectQualifier`, sem lógica paralela. O botão individual "Verificar site/CNPJ" passa a enfileirar o mesmo motor.
- **Normalização** de razão social, nome fantasia, telefones, URLs, endereço e categoria. Os valores originais ficam em `enrichment.analysis_original`, e contatos confirmados ou editados durante a análise não são sobrescritos.
- **Duplicados** dentro do lote e no restante do tenant, por CNPJ, place_id, telefone, domínio, Instagram, endereço ou nome + município, com o nível de confiança registrado. Nenhum registro é mesclado automaticamente; casos ambíguos vão para revisão.
- **Site**: o próprio site é lido para encontrar contatos comerciais, CNPJ e redes sociais. A BrasilAPI só é consultada com CNPJ válido, e as consultas têm cache. O Instagram é verificado só por links encontrados no site e pela Business Discovery oficial (desligada por padrão); não há scraping nem requisição anônima.
- **Atividade**: a ausência de site ou de redes sociais resulta em "situação indeterminada", nunca em empresa encerrada. Só o registro cadastral (por exemplo, BAIXADA) indica encerramento, e isso envia o prospect para revisão.
- **Scores**: score comercial e confiança cadastral de 0 a 100, com pesos e limites em `config/prospect-analysis.php` e `config/company-validation.php`, cada um acompanhado das justificativas. A prioridade é alta, média ou baixa, e a **revisão necessária** é uma categoria separada (duplicados, CNPJ inválido, situação cadastral conflitante ou dados insuficientes).
- **Jobs** (`AnalyzeProspectBatch`): um prospect por execução e um worker por tenant (lock). Cada item tem no máximo 3 tentativas, com espera de 11 minutos após falha de fonte e timeout de 70 s. O progresso fica salvo por item. O cancelamento é aplicado entre um prospect e outro, e a retomada usa uma nova geração, então jobs antigos são ignorados. O envio nunca usa a fila síncrona dentro de uma requisição HTTP.
- **Cache por fonte** (`ProspectSourceCache`), isolado por tenant: site 24 h, BrasilAPI 720 h, Instagram 24 h, falhas 10 min. Cada análise guarda uma impressão digital dos dados e da configuração. "Reanalisar apenas desatualizados ou com falhas" reaproveita as análises atuais.
- **Multitenancy**: lotes, itens, fontes e cache têm escopo de tenant. Prospects, buscas e lotes de outro tenant retornam 404 ou erro de validação.
- **Histórico**: evidências, fontes, datas, falhas e resultado de cada item ficam em `prospect_analysis_items` e em `enrichment.analysis`.
- **Interface** na página do lote, com o botão **"Analisar prospects"** (para o lote inteiro ou só para os selecionados), progresso, cancelamento e retomada. O painel mostra total, analisados, alta/média/baixa, revisão necessária, inconsistências, duplicados, pendentes e falhas, e cada indicador funciona como filtro. Cada linha mostra a prioridade calculada pela análise.
- **CRM**: os prospects selecionados são enviados pelo conversor existente. Itens em revisão são bloqueados e nenhuma mensagem é disparada.
- **Cuidados mantidos**: nenhum prospect é excluído por score baixo; nenhuma API paga é chamada, mesmo configurada com orçamento (há teste para isso); não há IA generativa no fluxo, só regras determinísticas; capturas e CRM continuam com o comportamento anterior.

## Verificação

- Suíte completa Pest em SQLite de teste: **1.318 testes aprovados, 13.715 assertions**. `ProspectAnalysisTest`: 18 testes, 122 assertions.
- Cenários sintéticos cobertos: duplicados e correspondências ambíguas, prospect sem site, CNPJ inválido, empresa ativa, CNPJ baixado, fonte externa indisponível (503) com retry limitado, cancelamento, retomada, concorrência, isolamento entre tenants, reaproveitamento de cache, contatos preservados e bloqueio no CRM.
- `tsc --noEmit`, ESLint, Prettier, Pint (`--test`) e `git diff --check`: aprovados. Corrigi a ordem de import e a formatação de `ideal-customer-profile.tsx`.
- `npm run build`: aprovado; os assets em `public/build` foram regenerados.
- Os testes usam fontes HTTP bloqueadas ou simuladas (`Http::preventStrayRequests`). Nenhum dado real foi consultado e nenhuma API comercial foi chamada.
- [batch-desktop.png](docs/vetorleads-prospect/batch-desktop.png) foi capturado antes de uma correção: nele as linhas mostram "Prioridade alta" enquanto o lote conta "Revisão necessária". O código já mostra a prioridade da análise, e um teste cobre isso, mas o screenshot novo não foi feito (ver Incidente).

## Incidente

Para refazer o screenshot, rodei `php artisan migrate:fresh --force` e um seed sintético achando que as variáveis de ambiente apontavam para um SQLite no scratchpad. Existe um `bootstrap/cache/config.php`, criado às 15:36:33 e não gerado por esta tarefa, que faz o Laravel ignorar essas variáveis. Esse cache aponta para **MySQL `vetorleads` em `localhost`**. O SQLite ficou vazio, então o comando provavelmente rodou nesse MySQL local.

Se o banco estava acessível, as tabelas foram recriadas vazias e receberam um tenant, o usuário `demo@example.test` e 4 prospects sintéticos. A leitura de confirmação foi bloqueada pela política de permissões, então o impacto **ainda não foi verificado**. É o banco local de desenvolvimento, não produção.

Pendências:

- Conferir o banco `vetorleads` local e restaurar de backup ou dump, se necessário.
- Encerrar o servidor PHP de teste (porta 8765) e o `queue:work database`, se ainda estiverem rodando.
- Refazer o screenshot com `APP_CONFIG_CACHE` apontando para um caminho vazio e confirmando o banco de destino antes de qualquer comando que escreva.

## Arquivos centrais

Novos: `ProspectAnalysis`, `ProspectSourceCache`, `AnalyzeProspectBatch`, `ProspectAnalysisBatch`, `ProspectAnalysisItem`, `ProspectAnalysisController`, `config/prospect-analysis.php`, migration `2026_10_20_170000_create_prospect_analysis_batches.php`, componente `prospect-analysis.tsx` e `tests/Feature/ProspectAnalysisTest.php`.

Integrados: `ProspectingController` (filtros e prioridade da análise), `ProspectEnricher`, `ProspectQualifier` (pesos configuráveis), `WebsiteProbe`, `BrasilApiCnpjLookup`, `ProspectConverter` (bloqueio de revisão), `prospecting-results.tsx`, `prospecting/prospects.tsx` e rotas `prospecting/analysis-batches`.

## Limitações

- O Instagram depende da Business Discovery oficial, desligada por padrão. Sem ela, só valem os links encontrados no site.
- A detecção de duplicados percorre os prospects do tenant a cada análise. Funciona para o limite de 5.000 por lote, mas pode exigir um índice dedicado em volumes maiores.
- A IA para casos ambíguos não foi ativada; esses casos ficam em revisão humana.

Não foi feito commit, push, deploy nem migração em ambiente aprovado, e nenhuma API paga foi habilitada. Para ativar, aplicar as migrations após backup, publicar o build e manter um worker da fila `database` em execução.
