# Execução — VETORLEADS-VALIDATE-01

Implementação local concluída em 20/10/2026, sem publicação. Validação empresarial integrada à prospecção e ao CRM do VetorLeads, com evidências, confiança separada de identidade/atividade, histórico, filas e limites de consulta.

## Comparação do correio

O `correio.md` da raiz mudou de **VETOR-CHAT-13.0** para **VETORLEADS-VALIDATE-01**. SHA-256 atual: `e1f981c1175beb3fa4a8690ccc93aa962f166768da614cc96c7a544a4205edfd`.

Instrução preservada em [correio.md](docs/vetorleads-validate-01/correio.md). O relatório anterior foi arquivado integralmente em [previous-executed.md](docs/vetorleads-validate-01/previous-executed.md), SHA-256 `d51ca9903bb8c4e0c26bf23957105cfd083bcfd8c6c6aae454ef2fa27eabceff`.

Os correios internos de VetorOS e VetorLeads não mudaram em relação aos commits anteriores à atualização. A solicitação executada é a da raiz. Base do código: VetorLeads `adcc6e4`, VetorOS `953b90a8`. Não houve alteração de código no VetorOS nesta tarefa.

## Implementação

- Projeção normalizada de nomes e variações, endereço, município/UF, telefones, domínio, CNPJ, identificador Maps e perfil Instagram. Dados capturados e payloads originais permanecem preservados.
- Correspondência determinística, com coincidências e divergências explicáveis: CNPJ 70 pontos, Maps 80, telefone 30, Instagram 30, endereço 20, domínio 20, nome semelhante 15, município 10 e UF 5. Pontuação limitada a 100; identidade exige pelo menos 60 pontos. Nome/localidade sozinhos não estabelecem identidade. Divergência de CNPJ ou Maps invalida a correspondência; localidade divergente limita a confiança quando não existe identificador exato.
- Endereços comparados por logradouro e número, evitando sinalizar mudança por diferenças de bairro/município acrescentadas ao texto. Comparação textual conservadora; não há geocodificação.
- Sete situações: atividade confirmada, provavelmente ativa, indeterminada, possivelmente encerrada, encerramento confirmado, mudança identificada e dados conflitantes. Situação cadastral ativa indica atividade provável, sem comprovar operação. Ausência de resultados nunca equivale a encerramento.
- Histórico por empresa com entrada normalizada, fontes, URLs, datas, dados observados, confiança, coincidências, divergências e falhas. Cada revalidação gera um registro; uso do cache não duplica histórico. A validação não sobrescreve contatos nem exclui leads antigos.
- Na listagem de captados: situação, confiança de identidade e atividade, última validação, filtro por situação, ordenação por confiança de atividade e ações **Validar empresa** / **Validar selecionadas**.
- Histórico acessível nos detalhes da empresa e na aba **Validação empresarial** da ficha do lead. Leads sem prospect vinculado recebem explicação na interface.
- Conversão de prospect ao CRM consulta a situação existente e exige revisão para encerramento, divergências ou mudança de endereço. Deduplicação já existente por tenant/CNPJ/Maps/contatos foi reutilizada. Novas reservas de automação, elegibilidade de candidatos e `contact_allowed` respeitam esses bloqueios. Não apaga execuções históricas nem cancela envios já realizados por sistemas externos.
- Revisão ocorre pela análise de evidências, correção dos dados na origem e revalidação; não há botão para forçar confirmação de atividade ou ignorar evidências.

## Fontes e limites

| Fonte | Comportamento implementado | Estado padrão |
| --- | --- | --- |
| BrasilAPI | Consulta exata de CNPJ conhecido; dados cadastrais sem quadro societário; cadastral ativa não comprova operação | Reutiliza configuração existente |
| Site da empresa | Reutiliza verificação com proteção SSRF, IP fixado e sem seguir redirecionamentos; extrai contatos, CNPJ, dados estruturados e link Instagram publicados | Reutiliza configuração existente |
| Pesquisa web | Adaptador autenticado Brave Search; resultados apresentados como referências, sem inferir encerramento a partir de snippets | Desativada |
| Instagram | Business Discovery oficial, com token e conta autorizados; consulta perfil conhecido ou link de site corroborado e considera publicação recente apenas após correspondência suficiente | Desativado |
| Google Places | Place Details por identificador conhecido, máscara explícita e orçamento; situação operacional estruturada | Desativado |

Não há busca de CNPJ por semelhança de nome. Um CNPJ encontrado no site pode constar como evidência, mas esta camada não o atribui automaticamente ao cadastro nem faz descoberta irrestrita de CNPJ. Pesquisa web não transforma referências não verificadas em fatos comerciais. Instagram não utiliza scraping; disponibilidade e permissões dependem da conta autorizada. Google Places e pesquisa web exigem configuração de custo e orçamento antes de qualquer chamada.

Configuração em [company-validation.php](gateway/vetorleads/config/company-validation.php): cache padrão de 168 horas, cache de falhas de 10 minutos, lotes de até 25 empresas, limite diário de 250 consultas por tenant. Mudança dos dados ou das fontes habilitadas invalida o cache. Consultas gratuitas precedem opcionais. Um job por empresa, deduplicação de jobs, lock de execução, timeout de 70 segundos e até três tentativas, com espera de 60/300 segundos para falhas transitórias. Falhas ficam no histórico sem derrubar o CRM; indisponibilidade sem nova evidência não remove bloqueio anterior.

Orçamento padrão **zero**. Valores de orçamento/custo são inteiros em milionésimos de dólar, por tenant/dia; cada chamada paga reserva um teto de custo dentro de transação serializada, inclusive quando a chamada falha. Isto controla o teto estimado configurado, não substitui a fatura do provedor. Não existe preço presumido nem conversão cambial automática. Credenciais e corpos de erro externos não são gravados no histórico de validação.

Variáveis opcionais: `COMPANY_VALIDATION_CACHE_HOURS`, `COMPANY_VALIDATION_DAILY_LIMIT`, `COMPANY_VALIDATION_DAILY_BUDGET_MICROS`; `COMPANY_VALIDATION_WEB_SEARCH_ENABLED`, `BRAVE_SEARCH_API_KEY`, `COMPANY_VALIDATION_WEB_SEARCH_COST_MICROS`; `COMPANY_VALIDATION_INSTAGRAM_ENABLED`, `INSTAGRAM_BUSINESS_ACCESS_TOKEN`, `INSTAGRAM_BUSINESS_ACCOUNT_ID`, `INSTAGRAM_GRAPH_VERSION`; `COMPANY_VALIDATION_PLACES_ENABLED`, `GOOGLE_PLACES_API_KEY`, `COMPANY_VALIDATION_PLACES_COST_MICROS`.

**Custo externo da homologação: zero.** Nenhum lead real foi consultado e nenhuma API comercial foi chamada. APIs externas foram simuladas nos testes; a documentação pública dos provedores foi consultada. Contratos, permissões, retenção e atribuição exigidos por cada provedor devem ser conferidos antes de habilitá-lo no ambiente operacional.

## Verificação

- Suíte completa: **1.298 testes aprovados, 13.574 assertions** — [resultado](docs/vetorleads-validate-01/tests-full.json).
- Após o último ajuste defensivo na leitura de dados estruturados dos sites: **99 testes aprovados, 567 assertions** — [resultado](docs/vetorleads-validate-01/tests-targeted.json).
- Casos sintéticos: ativa, encerrada, mudança de endereço, nome fantasia diferente da razão social, homônimas, CNPJ incompatível, ausência de presença digital, conflitos, fechamento temporário, referências não verificadas, falhas de provedor, cache, limites, custos, isolamento entre tenants, jobs com tenant incorreto, CRM e bloqueios de automação. Instagram autorizado, pesquisa web e Places foram testados com mocks; fontes pagas com orçamento zero não são chamadas.
- `npm run build`, TypeScript (`tsc --noEmit`), ESLint nos três arquivos frontend alterados, Pint nos arquivos PHP alterados e `git diff --check` aprovados. [Build](docs/vetorleads-validate-01/build.log), [Pint](docs/vetorleads-validate-01/pint.json).
- Migrations executadas em SQLite descartável e na suíte em memória; nenhum banco operacional foi migrado. [Evidência](docs/vetorleads-validate-01/migrations.log).
- Worker real processou `ValidateCompany` no banco descartável e encerrou com a fila vazia. [Evidência](docs/vetorleads-validate-01/worker.log).
- E2E em Chromium local, banco e usuário sintéticos: login, listagem, solicitação de validação, histórico, filtro, aba do lead e viewport móvel 390 × 844. Sem erros JavaScript nem transbordamento horizontal no viewport móvel. [Resultado](docs/vetorleads-validate-01/browser-result.json).
- Capturas: [listagem](docs/vetorleads-validate-01/prospects-desktop.png), [histórico](docs/vetorleads-validate-01/prospect-history-desktop.png), [aba do lead](docs/vetorleads-validate-01/lead-validation-desktop.png), [celular](docs/vetorleads-validate-01/lead-validation-mobile.png).

## Arquivos e ativação posterior

Novos: `CompanyValidation`, migration `2026_10_20_120000_create_company_validations_table.php`, serviços em `app/Services/CompanyValidation/`, job `ValidateCompany`, controller `CompanyValidationController`, `config/company-validation.php`, componente `company-validation.tsx` e `CompanyValidationTest.php`.

Integrações alteradas: models `ProspectingResult`, `Lead`, `LeadAutomationExecution`; controllers `ProspectingController`, `LeadController`, `Api/LeadAutomationStateController`; serviços existentes `BrasilApiCnpjLookup`, `WebsiteProbe`, `ProspectConverter`; listagem `prospecting-results.tsx`, ficha `leads/edit.tsx` e `routes/web.php`. [Rotas](docs/vetorleads-validate-01/routes.log).

Não foi feito commit, push, deploy, alteração de infraestrutura, habilitação de APIs pagas, conexão WhatsApp ou envio de mensagens. As alterações anteriores do workspace e dos demais submódulos foram preservadas. Dependências foram instaladas localmente para verificação; os assets compilados e o `yarn.lock` alterados pela instalação/build de verificação foram restaurados, evitando incluir arquivos gerados na entrega. A stack Docker gera seu próprio build durante a construção da imagem.

Para disponibilizar posteriormente no ambiente aprovado: gerar a imagem/build a partir destas alterações, aplicar `php artisan migrate --force` com backup no banco de destino e garantir worker da fila configurada. APIs opcionais permanecem desligadas até configuração de credenciais, permissões e orçamento. A validação operacional com contas reais desses provedores não foi realizada nesta entrega.
